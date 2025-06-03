<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}


if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    
    if (isset($_COOKIE['lembrar_me_seletor']) && isset($_COOKIE['lembrar_me_validador'])) {
        
        require_once 'conexao.php'; 

        $seletor_cookie = $_COOKIE['lembrar_me_seletor'];
        $validador_cookie = $_COOKIE['lembrar_me_validador'];

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
                       
                        if (password_verify($validador_cookie, $token_hash_db)) {
                            
                            session_regenerate_id(true); 
                            $_SESSION['loggedin'] = true;
                            $_SESSION['id_usuario'] = $usuario_id_db;
                            $_SESSION['usuario'] = $nome_usuario_db;

                        } else {
                            
                            $sql_delete_token = "DELETE FROM tokens_lembrar_me WHERE seletor = ?";
                            if($stmt_delete_specific = mysqli_prepare($conexao, $sql_delete_token)){
                                mysqli_stmt_bind_param($stmt_delete_specific, "s", $seletor_cookie);
                                mysqli_stmt_execute($stmt_delete_specific);
                                mysqli_stmt_close($stmt_delete_specific);
                            }
                            setcookie('lembrar_me_seletor', '', time() - 3600, '/'); 
                            setcookie('lembrar_me_validador', '', time() - 3600, '/'); 
                        }
                    }
                }
            } else {
                
                setcookie('lembrar_me_seletor', '', time() - 3600, '/');
                setcookie('lembrar_me_validador', '', time() - 3600, '/');
            }
            mysqli_stmt_close($stmt); 
        } else {
            error_log("Erro ao executar a busca por token lembrar-me: " . mysqli_stmt_error($stmt));
        }
        mysqli_close($conexao); 
    }
}

?>