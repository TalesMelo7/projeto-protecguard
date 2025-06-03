<?php
require_once 'php/conexao.php'; // Inclui a conexão com o banco

$token_valido = false;
$mensagem_erro = '';
$email_usuario = '';
$token_url = '';

if (isset($_GET['token']) && isset($_GET['email'])) {
    $token_url = trim($_GET['token']);
    $email_usuario = trim($_GET['email']);

    if (empty($token_url) || empty($email_usuario)) {
        $mensagem_erro = "Token ou e-mail não fornecido corretamente.";
    } else {
        $sql_check_token = "SELECT email FROM password_resets WHERE token = ? AND email = ? AND expires_at >= NOW()";
        if ($stmt_check = mysqli_prepare($conexao, $sql_check_token)) {
            mysqli_stmt_bind_param($stmt_check, "ss", $token_url, $email_usuario);
            mysqli_stmt_execute($stmt_check);
            mysqli_stmt_store_result($stmt_check);

            if (mysqli_stmt_num_rows($stmt_check) == 1) {
                $token_valido = true;
            } else {
                $mensagem_erro = "Link de redefinição de senha inválido, expirado ou já utilizado. Por favor, solicite um novo.";
            }
            mysqli_stmt_close($stmt_check);
        } else {
            $mensagem_erro = "Erro ao verificar o token. Tente novamente mais tarde.";
            error_log("Erro ao preparar statement para verificar token de reset: " . mysqli_error($conexao));
        }
    }
} else {
    $mensagem_erro = "Link de redefinição de senha incompleto. Certifique-se de usar o link completo enviado para o seu e-mail.";
}
mysqli_close($conexao);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redefinir Senha - ProtecGuard</title>
    <link rel="stylesheet" href="Styles/Styles8.css">
</head>
<body>
    <header class="cabecalho-principal">
        <div class="container">
            <div class="logo-empresa">
                <a href="index.php"><img src="images/logo.png" alt="Imagem do Logo"></a>
            </div>
            <nav>
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="alarmes.html">Alarmes</a></li>
                    <li><a href="cameras.html">Câmeras</a></li>
                    <li><a href="sistemas.html">Sistemas</a></li>
                    <li><a href="login.html">Login</a></li>
                </ul>
            </nav>
        </div>
        <div class="divisor-diagonal"></div>
    </header>

    <main>
        <div class="redefinir">
            <div class="container-redefinir">
                <h2>Redefinir Senha</h2>

                <?php if ($token_valido): ?>
                    <p>Olá! Por favor, insira sua nova senha abaixo.</p>
                    <form id="form-redefinir-senha">
                        <input type="hidden" name="email" value="<?php echo htmlspecialchars($email_usuario); ?>">
                        <input type="hidden" name="token" value="<?php echo htmlspecialchars($token_url); ?>">

                        <div>
                            <label for="nova-senha">Nova Senha:</label>
                            <input type="password" id="nova-senha" name="nova_senha" required>
                        </div>
                        <div>
                            <label for="confirmar-nova-senha">Confirmar Nova Senha:</label>
                            <input type="password" id="confirmar-nova-senha" name="confirmar_nova_senha" required>
                        </div>
                        <div id="mensagem-feedback-redefinir"></div>
                        <button type="submit">Salvar Nova Senha</button>
                    </form>
                <?php else: ?>
                    <div id="mensagem-feedback-redefinir" class="erro">
                        <?php echo htmlspecialchars($mensagem_erro); ?>
                    </div>
                    <div class="link-voltar">
                        <p><a href="recuperar-senha.html">Tentar solicitar novamente</a> ou <a href="login.html">Voltar para o Login</a></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <footer class="rodape">
        <p class="rodape-texto">© 2024 ProtecGuard. Todos os direitos reservados.</p>
    </footer>

    <script src="js/app_redefinir_senha.js" defer></script>
</body>
</html>