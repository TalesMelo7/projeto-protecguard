<?php
// Iniciar a sessão para poder manipulá-la
session_start();

// Incluir o arquivo de conexão com o banco de dados,
// pois precisaremos dele para deletar o token "lembrar-me" do banco.
require_once 'conexao.php'; // Garanta que este arquivo não produz saída em caso de sucesso.

// 1. Limpar os cookies "lembrar-me" se existirem
if (isset($_COOKIE['lembrar_me_seletor'])) {
    $seletor_cookie = $_COOKIE['lembrar_me_seletor'];
    
    // Deletar o token correspondente do banco de dados
    // Isso é importante para invalidar o token e evitar que seja reutilizado.
    $sql_delete_token = "DELETE FROM tokens_lembrar_me WHERE seletor = ?";
    if ($stmt_delete = mysqli_prepare($conexao, $sql_delete_token)) {
        mysqli_stmt_bind_param($stmt_delete, "s", $seletor_cookie);
        mysqli_stmt_execute($stmt_delete);
        mysqli_stmt_close($stmt_delete);
    } else {
        // Logar erro se a preparação da query falhar
        error_log("Erro ao preparar a query para deletar token: " . mysqli_error($conexao));
    }
    
    // Instruir o navegador a deletar os cookies setando uma data de expiração no passado
    setcookie('lembrar_me_seletor', '', time() - 3600, '/'); // O '/' garante que o cookie é para todo o site
    setcookie('lembrar_me_validador', '', time() - 3600, '/');
}

// Fechar a conexão com o banco de dados, já que não será mais usada neste script
mysqli_close($conexao);


// 2. Limpar todas as variáveis da sessão
$_SESSION = array(); // Sobrescreve o array da sessão com um array vazio

// 3. Se é desejável destruir a sessão completamente, apague também o cookie de sessão.
// Nota: Isso destruirá a sessão, e não apenas os dados de sessão!
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 4. Finalmente, destruir a sessão.
session_destroy();

// 5. Redirecionar o usuário para a página de login ou para a página inicial.
// Escolha para onde você quer que o usuário vá após o logout.
// A página inicial (index.php) é uma boa opção, pois ela já mostrará o link "Login".
header("Location: ../index.php"); // Redireciona para a index.php na raiz do projeto
exit; // Garante que nenhum código adicional seja executado após o redirecionamento
?>