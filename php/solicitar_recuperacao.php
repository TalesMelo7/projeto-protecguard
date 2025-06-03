<?php
require_once 'conexao.php';

// Definir o tipo de conteúdo da resposta para JSON
header('Content-Type: application/json; charset=utf-8');

// Array para a resposta JSON
$resposta = [
    'status' => 'erro',
    'mensagem' => 'Ocorreu um erro inesperado.',
    'link_debug' => null,
    'token_debug' => null,
    'expira_debug' => null
];

$mensagem_padrao_usuario = "Se o seu endereço de e-mail estiver em nosso banco de dados, instruções para redefinir sua senha serão preparadas.";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';

    if (empty($email)) {
        $resposta['mensagem'] = "Erro: O campo e-mail é obrigatório.";
        echo json_encode($resposta);
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $resposta['mensagem'] = "Erro: Formato de e-mail inválido.";
        echo json_encode($resposta);
        exit;
    }

    $sql_check_email = "SELECT id FROM usuarios WHERE email = ?";
    if ($stmt_check = mysqli_prepare($conexao, $sql_check_email)) {
        mysqli_stmt_bind_param($stmt_check, "s", $email);
        mysqli_stmt_execute($stmt_check);
        mysqli_stmt_store_result($stmt_check);

        if (mysqli_stmt_num_rows($stmt_check) == 1) {
            mysqli_stmt_fetch($stmt_check);
            mysqli_stmt_close($stmt_check);

            try {
                $token = bin2hex(random_bytes(32));
            } catch (Exception $e) {
                $token = bin2hex(openssl_random_pseudo_bytes(32));
            }
            
            $expira_em_timestamp = time() + 3600; 
            $expira_em_data_db = date('Y-m-d H:i:s', $expira_em_timestamp);

            $sql_delete_old = "DELETE FROM password_resets WHERE email = ?";
            if($stmt_delete = mysqli_prepare($conexao, $sql_delete_old)){
                mysqli_stmt_bind_param($stmt_delete, "s", $email);
                mysqli_stmt_execute($stmt_delete);
                mysqli_stmt_close($stmt_delete);
            }

            $sql_insert_token = "INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)";
            if ($stmt_insert = mysqli_prepare($conexao, $sql_insert_token)) {
                mysqli_stmt_bind_param($stmt_insert, "sss", $email, $token, $expira_em_data_db);
                
                if (mysqli_stmt_execute($stmt_insert)) {
                    $link_redefinicao = "http://localhost/protecguard/redefinir_senha.php?token=" . $token . "&email=" . urlencode($email);
                    
                    $resposta['status'] = 'sucesso';
                    $resposta['mensagem'] = $mensagem_padrao_usuario;
                    $resposta['link_debug'] = $link_redefinicao;
                    $resposta['token_debug'] = $token;
                    $resposta['expira_debug'] = $expira_em_data_db;

                } else {
                    error_log("Erro ao inserir token de reset: " . mysqli_stmt_error($stmt_insert));
                    $resposta['mensagem'] = "Não foi possível processar sua solicitação de redefinição. Tente novamente.";
                }
                mysqli_stmt_close($stmt_insert);
            } else {
                error_log("Erro ao preparar statement para inserir token: " . mysqli_error($conexao));
                $resposta['mensagem'] = "Erro interno do servidor ao processar a solicitação.";
            }
        } else {
            mysqli_stmt_close($stmt_check);
            $resposta['status'] = 'sucesso_sem_match';
            $resposta['mensagem'] = $mensagem_padrao_usuario;
        }
    } else {
        error_log("Erro ao preparar statement para verificar email: " . mysqli_error($conexao));
        $resposta['mensagem'] = "Erro crítico no servidor. Tente mais tarde.";
    }
    mysqli_close($conexao);
} else {
    $resposta['mensagem'] = "Erro: Método de requisição inválido.";
}

echo json_encode($resposta);
?>