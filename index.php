<?php
include('conexao.php');
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta http-equiv-"X-UA-Compatible" content-"IE-edge">
        <meta name-"viewport" content"width-device-width, initial-scale-1.0"
        <title>login</title>
    </head>
    <body>
        <form action="" method="POST">
            <p>
                <label>$usuario</label>
                <input type="text" name="usuario">
            </p>
            <p>
                <label>$senha</label>
                <input type="password" name="senha">
            </p>
            <p>
                <button type="submit">Entrar</button>
            </p>
        </form>
    </body>
</html>