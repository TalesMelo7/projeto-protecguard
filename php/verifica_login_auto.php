<?php
// Iniciar a sessão se ainda não estiver iniciada (deve ser a primeira coisa no script)
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Incluir o arquivo de conexão apenas se precisarmos verificar o cookie,
// e somente se o usuário já não estiver logado pela sessão.
// Se já está logado pela sessão, não precisamos do DB aqui.

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    // Usuário não está logado pela sessão normal, verificar cookie "lembrar-me"
    if (isset($_COOKIE['lembrar_me_seletor']) && isset($_COOKIE['lembrar_me_validador'])) {
        
        require_once 'conexao.php'; // $conexao estará disponível aqui

        $seletor_cookie = $_COOKIE['lembrar_me_seletor'];
        $validador_cookie = $_COOKIE['lembrar_me_validador'];

        // Preparar a consulta para buscar o token no banco de dados
        // É importante verificar também se o token não expirou (expira_em >= NOW())
        $sql = "SELECT t.id as token_id, t.usuario_id, t.token_hash, t.expira_em, u.usuario 
                FROM tokens_lembrar_me t
                JOIN usuarios u ON t.usuario_id = u.id
                WHERE t.seletor = ? AND t.expira_em >= NOW()";

        if ($stmt = mysqli_prepare($conexao, $sql)) {
            mysqli_stmt_bind_param($stmt, "s", $seletor_cookie);
            
            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_store_result($stmt);

                if (mysqli_stmt_num_rows($stmt) == 1) {
                    mysqli_stmt_bind_result($stmt, $token_id_db, $usuario_id_db, $token_hash_db, $expira_em_db, $nome_usuario_db);
                    
                    if (mysqli_stmt_fetch($stmt)) {
                        // Verificar o validador do cookie com o hash do validador armazenado no DB
                        if (password_verify($validador_cookie, $token_hash_db)) {
                            // Token válido! Usuário autenticado via cookie.
                            // Recriar a sessão para o usuário
                            session_regenerate_id(true); // Importante para segurança
                            $_SESSION['loggedin'] = true;
                            $_SESSION['id_usuario'] = $usuario_id_db;
                            $_SESSION['usuario'] = $nome_usuario_db;

                            // Opcional, mas recomendado: Renovar o token para maior segurança
                            // Gerar um novo validador, atualizar o hash no DB para o mesmo seletor,
                            // e reenviar o cookie com o novo validador.
                            // Isso ajuda a prevenir que um token roubado seja usado indefinidamente.
                            // (Lógica de renovação omitida aqui para simplicidade inicial, mas considere para produção)

                            // Exemplo simples de como poderia ser uma renovação:
                            // $novo_validador = gerar_token_seguro(32); // Supondo que gerar_token_seguro() está disponível
                            // $novo_validador_hash = password_hash($novo_validador, PASSWORD_DEFAULT);
                            // $nova_expiracao_timestamp = time() + (86400 * 30); // Renova por mais 30 dias
                            // $nova_expiracao_data_db = date('Y-m-d H:i:s', $nova_expiracao_timestamp);
                            
                            // $sql_update_token = "UPDATE tokens_lembrar_me SET token_hash = ?, expira_em = ? WHERE id = ?";
                            // if ($stmt_update = mysqli_prepare($conexao, $sql_update_token)) {
                            //     mysqli_stmt_bind_param($stmt_update, "ssi", $novo_validador_hash, $nova_expiracao_data_db, $token_id_db);
                            //     mysqli_stmt_execute($stmt_update);
                            //     mysqli_stmt_close($stmt_update);
                                
                            //     setcookie('lembrar_me_validador', $novo_validador, $nova_expiracao_timestamp, '/', '', false, true);
                            // }
                        } else {
                            // Validador inválido. Alguém pode estar tentando usar um cookie adulterado.
                            // É uma boa prática invalidar este token e remover os cookies.
                            $sql_delete_token = "DELETE FROM tokens_lembrar_me WHERE seletor = ?";
                            if($stmt_delete_specific = mysqli_prepare($conexao, $sql_delete_token)){
                                mysqli_stmt_bind_param($stmt_delete_specific, "s", $seletor_cookie);
                                mysqli_stmt_execute($stmt_delete_specific);
                                mysqli_stmt_close($stmt_delete_specific);
                            }
                            setcookie('lembrar_me_seletor', '', time() - 3600, '/'); // Expirar cookie
                            setcookie('lembrar_me_validador', '', time() - 3600, '/'); // Expirar cookie
                        }
                    }
                }
            } else {
                // Seletor não encontrado no DB ou token expirado. Limpar cookies do navegador.
                setcookie('lembrar_me_seletor', '', time() - 3600, '/');
                setcookie('lembrar_me_validador', '', time() - 3600, '/');
            }
            mysqli_stmt_close($stmt); // Fechar o statement principal da busca
        } else {
            error_log("Erro ao executar a busca por token lembrar-me: " . mysqli_stmt_error($stmt));
        }
        mysqli_close($conexao); // Fechar a conexão, já que foi aberta aqui dentro
    }
}

// Ao final deste script, se o usuário estiver logado (seja por sessão preexistente ou
// por cookie "lembrar-me" válido), as variáveis $_SESSION['loggedin'], $_SESSION['id_usuario']
// e $_SESSION['usuario'] estarão definidas. Caso contrário, não estarão.
?>