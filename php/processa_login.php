<?php
// Iniciar a sessão PHP no topo
session_start();

require_once 'conexao.php'; // Inclui a conexão com o banco

function gerar_token_seguro($tamanho = 32) {
    return bin2hex(random_bytes($tamanho));
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $usuario_input = isset($_POST['usuario']) ? trim($_POST['usuario']) : '';
    $senha_input = isset($_POST['senha']) ? trim($_POST['senha']) : '';
    $manter_conectado = isset($_POST['manter-conectado']) && $_POST['manter-conectado'] === 'sim';

    if (empty($usuario_input) || empty($senha_input)) {
        echo "Erro: Nome de usuário e senha são obrigatórios.";
        exit;
    }

    $sql = "SELECT id, usuario, senha FROM usuarios WHERE usuario = ?";

    if ($stmt = mysqli_prepare($conexao, $sql)) {
        mysqli_stmt_bind_param($stmt, "s", $usuario_input);

        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_store_result($stmt);

            if (mysqli_stmt_num_rows($stmt) == 1) {
                mysqli_stmt_bind_result($stmt, $id_usuario_db, $nome_usuario_db, $senha_hash_db);

                if (mysqli_stmt_fetch($stmt)) {
                    if (password_verify($senha_input, $senha_hash_db)) {
                        session_regenerate_id(true);

                        $_SESSION['loggedin'] = true;
                        $_SESSION['id_usuario'] = $id_usuario_db;
                        $_SESSION['usuario'] = $nome_usuario_db;

                        $mensagem_resposta = "Login bem-sucedido! Bem-vindo, " . htmlspecialchars($nome_usuario_db) . ".";

                        if ($manter_conectado) {
                            $seletor = gerar_token_seguro(12);
                            $validador = gerar_token_seguro(32);
                            $validador_hash = password_hash($validador, PASSWORD_DEFAULT);
                            $expira_em_timestamp = time() + (86400 * 30); // 30 dias
                            $expira_em_data_db = date('Y-m-d H:i:s', $expira_em_timestamp);

                            $sql_delete_tokens = "DELETE FROM tokens_lembrar_me WHERE usuario_id = ?";
                            if($stmt_delete = mysqli_prepare($conexao, $sql_delete_tokens)){
                                mysqli_stmt_bind_param($stmt_delete, "i", $id_usuario_db);
                                mysqli_stmt_execute($stmt_delete);
                                mysqli_stmt_close($stmt_delete);
                            }

                            $sql_insert_token = "INSERT INTO tokens_lembrar_me (usuario_id, seletor, token_hash, expira_em) VALUES (?, ?, ?, ?)";
                            if ($stmt_token = mysqli_prepare($conexao, $sql_insert_token)) {
                                mysqli_stmt_bind_param($stmt_token, "isss", $id_usuario_db, $seletor, $validador_hash, $expira_em_data_db);
                                if(mysqli_stmt_execute($stmt_token)){
                                    setcookie('lembrar_me_seletor', $seletor, $expira_em_timestamp, '/', '', false, true); // Mude secure para true em HTTPS
                                    setcookie('lembrar_me_validador', $validador, $expira_em_timestamp, '/', '', false, true); // Mude secure para true em HTTPS
                                    $mensagem_resposta .= " Você será lembrado.";
                                } else {
                                    error_log("Falha ao inserir token lembrar-me para usuario_id: " . $id_usuario_db . " Erro: " . mysqli_stmt_error($stmt_token));
                                    $mensagem_resposta .= " (Não foi possível ativar 'Manter-me conectado').";
                                }
                                mysqli_stmt_close($stmt_token);
                            } else {
                                 error_log("Falha ao preparar insert token para usuario_id: " . $id_usuario_db . " Erro: " . mysqli_error($conexao));
                                 $mensagem_resposta .= " (Erro ao configurar 'Manter-me conectado').";
                            }
                        }
                        echo $mensagem_resposta;

                    } else {
                        echo "Erro: Senha inválida.";
                    }
                }
            } else {
                echo "Erro: Usuário não encontrado.";
            }
        } else {
            error_log("Erro ao executar a busca por usuário: " . mysqli_stmt_error($stmt));
            echo "Erro: Problema ao tentar fazer login.";
        }
        mysqli_stmt_close($stmt);
    } else {
        error_log("Erro ao preparar a busca por usuário: " . mysqli_error($conexao));
        echo "Erro: Problema crítico no servidor.";
    }

    mysqli_close($conexao);

} else {
    echo "Erro: Método de requisição inválido.";
}
?>