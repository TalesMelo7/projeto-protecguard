<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
   
    $nome = htmlspecialchars(trim($_POST['nome']));
    $email = htmlspecialchars(trim($_POST['email']));
    $telefone = htmlspecialchars(trim($_POST['telefone']));
    $solucao_desejada = htmlspecialchars(trim($_POST['solucao_desejada']));

    if (empty($nome) || empty($email) || empty($telefone) || empty($solucao_desejada)) {
        echo "Por favor, preencha todos os campos obrigatórios.";
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "Formato de e-mail inválido.";
        exit;
    }

    $destinatario = "protecguard2025@gmail.com"; 
    $assunto = "Nova Solicitação de Orçamento de: " . $nome;

    $corpo_email = "Você recebeu uma nova solicitação de orçamento:\n\n";
    $corpo_email .= "Nome: " . $nome . "\n";
    $corpo_email .= "E-mail: " . $email . "\n";
    $corpo_email .= "Telefone: " . $telefone . "\n";
    $corpo_email .= "Solução Desejada:\n" . $solucao_desejada . "\n";

    $headers = "From: " . $email . "\r\n";
    $headers .= "Reply-To: " . $email . "\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

    if (mail($destinatario, $assunto, $corpo_email, $headers)) {
        echo "<h1>Obrigado!</h1>";
        echo "<p>Sua solicitação de orçamento foi enviada com sucesso. Entraremos em contato em breve.</p>";
        
    } else {
        echo "Desculpe, ocorreu um erro ao enviar sua solicitação. Tente novamente mais tarde.";
    }

} else {
    
    echo "Acesso inválido.";
}
?>