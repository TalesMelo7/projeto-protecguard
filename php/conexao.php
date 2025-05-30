<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

define('DB_SERVIDOR', 'localhost');
define('DB_USUARIO', 'root');
define('DB_SENHA', '');
define('DB_NOME_BANCO', 'protecguard');

$conexao = mysqli_connect(DB_SERVIDOR, DB_USUARIO, DB_SENHA, DB_NOME_BANCO);

if ($conexao === false) {
    die("Falha crítica na conexão com o banco de dados: " . mysqli_connect_error());
}

mysqli_set_charset($conexao, "utf8mb4");
?>