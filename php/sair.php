<?php

session_start();

require_once 'conexao.php'; 

if (isset($_COOKIE['lembrar_me_seletor'])) {
    $seletor_cookie = $_COOKIE['lembrar_me_seletor'];
    
    $sql_delete_token = "DELETE FROM tokens_lembrar_me WHERE seletor = ?";
    if ($stmt_delete = mysqli_prepare($conexao, $sql_delete_token)) {
        mysqli_stmt_bind_param($stmt_delete, "s", $seletor_cookie);
        mysqli_stmt_execute($stmt_delete);
        mysqli_stmt_close($stmt_delete);
    } else {
        
        error_log("Erro ao preparar a query para deletar token: " . mysqli_error($conexao));
    }
    
    setcookie('lembrar_me_seletor', '', time() - 3600, '/'); 
    setcookie('lembrar_me_validador', '', time() - 3600, '/');
}

mysqli_close($conexao);


$_SESSION = array(); 

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

session_destroy();

header("Location: ../index.php"); 
exit; 
?>