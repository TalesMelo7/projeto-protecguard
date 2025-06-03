<?php
require_once 'conexao.php'; 


header('Content-Type: text/plain; charset=utf-8');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $token = isset($_POST['token']) ? trim($_POST['token']) : '';
    $nova_senha = isset($_POST['nova_senha']) ? $_POST['nova_senha'] : '';
    $confirmar_nova_senha = isset($_POST['confirmar_nova_senha']) ? $_POST['confirmar_nova_senha'] : '';

    // Validações iniciais dos dados recebidos
    if (empty($email) || empty($token) || empty($nova_senha) || empty($confirmar_nova_senha)) {
        echo "Erro: Todos os campos são obrigatórios (incluindo token e email, que deveriam vir do formulário).";
        exit;
    }

    if ($nova_senha !== $confirmar_nova_senha) {
        echo "Erro: As novas senhas não coincidem.";
        exit;
    }

    if (strlen($nova_senha) < 6) {
        echo "Erro: A nova senha deve ter pelo menos 6 caracteres.";
        exit;
    }

    // Revalidar o token no banco de dados
    $sql_check_token = "SELECT u.id FROM password_resets pr JOIN usuarios u ON pr.email = u.email WHERE pr.email = ? AND pr.token = ? AND pr.expires_at >= NOW()";
    $user_id_from_token = null;

    if ($stmt_check = mysqli_prepare($conexao, $sql_check_token)) {
        mysqli_stmt_bind_param($stmt_check, "ss", $email, $token);
        mysqli_stmt_execute($stmt_check);
        mysqli_stmt_store_result($stmt_check);

        if (mysqli_stmt_num_rows($stmt_check) == 1) {
            mysqli_stmt_bind_result($stmt_check, $user_id_from_token);
            mysqli_stmt_fetch($stmt_check);
            // Token válido e usuário encontrado!
        } else {
            echo "Erro: Link de redefinição inválido, expirado ou já utilizado. Por favor, solicite um novo.";
            mysqli_stmt_close($stmt_check);
            mysqli_close($conexao);
            exit;
        }
        mysqli_stmt_close($stmt_check);
    } else {
        error_log("Erro ao preparar statement para validar token em processa_nova_senha: " . mysqli_error($conexao));
        echo "Erro: Falha ao validar o token. Tente novamente.";
        mysqli_close($conexao);
        exit;
    }

    // Se o token é válido e temos o user_id_from_token
    if ($user_id_from_token) {
        // Criptografar a nova senha
        $nova_senha_hasheada = password_hash($nova_senha, PASSWORD_DEFAULT);

        // Atualizar a senha do usuário na tabela 'usuarios'
        $sql_update_senha = "UPDATE usuarios SET senha = ? WHERE id = ?";
        if ($stmt_update = mysqli_prepare($conexao, $sql_update_senha)) {
            mysqli_stmt_bind_param($stmt_update, "si", $nova_senha_hasheada, $user_id_from_token);
            
            if (mysqli_stmt_execute($stmt_update)) {
                // Senha atualizada com sucesso! Agora, deletar o token da tabela password_resets.
                $sql_delete_token = "DELETE FROM password_resets WHERE email = ? OR token = ?"; // Deletar todos os tokens para este email ou este token específico
                if ($stmt_delete = mysqli_prepare($conexao, $sql_delete_token)) {
                    mysqli_stmt_bind_param($stmt_delete, "ss", $email, $token);
                    mysqli_stmt_execute($stmt_delete);
                    mysqli_stmt_close($stmt_delete);
                } else {
                    // Logar que não conseguiu deletar o token, mas a senha foi alterada
                    error_log("Senha alterada para email $email, mas falha ao deletar token de reset: " . mysqli_error($conexao));
                }
                echo "Sucesso! Sua senha foi redefinida. Você já pode fazer login com a nova senha.";

            } else {
                error_log("Erro ao atualizar senha para user_id $user_id_from_token: " . mysqli_stmt_error($stmt_update));
                echo "Erro: Não foi possível atualizar sua senha. Tente novamente.";
            }
            mysqli_stmt_close($stmt_update);
        } else {
            error_log("Erro ao preparar statement para atualizar senha: " . mysqli_error($conexao));
            echo "Erro: Falha crítica no servidor ao tentar atualizar senha.";
        }
    } else {
        // Isso não deveria acontecer se a validação do token acima foi bem-sucedida, mas é uma salvaguarda.
        echo "Erro: Não foi possível verificar a validade da sua solicitação de redefinição de senha.";
    }

    mysqli_close($conexao);

} else {
    echo "Erro: Método de requisição inválido.";
}
?>