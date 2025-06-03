<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'conexao.php'; 

echo "<h1>Criador de Usuário de Teste (Após Recriar BD)</h1>";

$nomeUsuarioTeste = "teste";
$senhaUsuarioTeste = "senha123";
$emailUsuarioTeste = "teste@exemplo.com";
$senhaHasheada = password_hash($senhaUsuarioTeste, PASSWORD_DEFAULT);

echo "<p>Tentando criar usuário: '" . htmlspecialchars($nomeUsuarioTeste) . "'...</p>";

$sql = "INSERT INTO usuarios (usuario, senha, email) VALUES (?, ?, ?)";

if ($stmt = mysqli_prepare($conexao, $sql)) {
    mysqli_stmt_bind_param($stmt, "sss", $nomeUsuarioTeste, $senhaHasheada, $emailUsuarioTeste);
    if (mysqli_stmt_execute($stmt)) {
        echo "<p style='color: green; font-weight: bold;'>SUCESSO: Usuário de teste '" . htmlspecialchars($nomeUsuarioTeste) . "' criado!</p>";
    } else {
        echo "<p style='color: red; font-weight: bold;'>ERRO ao criar usuário: " . htmlspecialchars(mysqli_stmt_error($stmt)) . "</p>";
    }
    mysqli_stmt_close($stmt);
} else {
    echo "<p style='color: red; font-weight: bold;'>ERRO ao preparar SQL: " . htmlspecialchars(mysqli_error($conexao)) . "</p>";
}
mysqli_close($conexao);
echo "<hr><p>Lembre-se de deletar ou renomear este script após o uso.</p>";
?>
