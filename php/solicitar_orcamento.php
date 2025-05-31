<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 1. Coletar os dados do formulário de forma segura
    $nome = htmlspecialchars(trim($_POST['nome']));
    $email = htmlspecialchars(trim($_POST['email']));
    $telefone = htmlspecialchars(trim($_POST['telefone']));
    $solucao_desejada = htmlspecialchars(trim($_POST['solucao_desejada']));

    // 2. Validar os dados (exemplos básicos)
    if (empty($nome) || empty($email) || empty($telefone) || empty($solucao_desejada)) {
        echo "Por favor, preencha todos os campos obrigatórios.";
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "Formato de e-mail inválido.";
        exit;
    }

    // 3. Montar o corpo do e-mail (exemplo)
    $destinatario = "protecguard2025@gmail.com"; // Substitua pelo seu e-mail
    $assunto = "Nova Solicitação de Orçamento de: " . $nome;

    $corpo_email = "Você recebeu uma nova solicitação de orçamento:\n\n";
    $corpo_email .= "Nome: " . $nome . "\n";
    $corpo_email .= "E-mail: " . $email . "\n";
    $corpo_email .= "Telefone: " . $telefone . "\n";
    $corpo_email .= "Solução Desejada:\n" . $solucao_desejada . "\n";

    $headers = "From: " . $email . "\r\n";
    $headers .= "Reply-To: " . $email . "\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

    // 4. Enviar o e-mail
    if (mail($destinatario, $assunto, $corpo_email, $headers)) {
        echo "<h1>Obrigado!</h1>";
        echo "<p>Sua solicitação de orçamento foi enviada com sucesso. Entraremos em contato em breve.</p>";
        // Você pode redirecionar para uma página de agradecimento:
        // header("Location: obrigado.html");
        // exit;
    } else {
        echo "Desculpe, ocorreu um erro ao enviar sua solicitação. Tente novamente mais tarde.";
    }

} else {
    // Se alguém tentar acessar o script diretamente sem enviar o formulário
    echo "Acesso inválido.";
}
?>