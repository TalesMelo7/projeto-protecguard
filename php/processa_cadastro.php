<?php
// Incluir o arquivo de conexão com o banco de dados
require_once 'conexao.php'; // A variável $conexao estará disponível aqui

// Definir o tipo de conteúdo da resposta para texto simples (para o fetch do JS)
header('Content-Type: text/plain; charset=utf-8');

// Verificar se os dados foram enviados via POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Obter os dados do formulário e fazer uma limpeza básica
    $usuario = isset($_POST['usuario']) ? trim($_POST['usuario']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $senha = isset($_POST['senha']) ? $_POST['senha'] : ''; // Senha não deve ter trim() inicialmente
    $confirmar_senha = isset($_POST['confirmar_senha']) ? $_POST['confirmar_senha'] : '';

    // --- Validações ---
    $erros = []; // Array para armazenar mensagens de erro

    // 1. Verificar campos vazios
    if (empty($usuario)) {
        $erros[] = "O nome de usuário é obrigatório.";
    }
    if (empty($email)) {
        $erros[] = "O e-mail é obrigatório.";
    }
    if (empty($senha)) {
        $erros[] = "A senha é obrigatória.";
    }
    if (empty($confirmar_senha)) {
        $erros[] = "A confirmação de senha é obrigatória.";
    }

    // 2. Validar formato do e-mail
    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erros[] = "Formato de e-mail inválido.";
    }

    // 3. Verificar se as senhas coincidem
    if ($senha !== $confirmar_senha) {
        $erros[] = "As senhas não coincidem.";
    }

    // 4. (Opcional) Validar força da senha (ex: mínimo de 6 caracteres)
    if (!empty($senha) && strlen($senha) < 6) {
        $erros[] = "A senha deve ter pelo menos 6 caracteres.";
    }

    // Se não houver erros de validação básica até aqui, verificar no banco de dados
    if (empty($erros)) {
        // 5. Verificar se o NOME DE USUÁRIO já existe
        $sql_check_usuario = "SELECT id FROM usuarios WHERE usuario = ?";
        if ($stmt_check_usuario = mysqli_prepare($conexao, $sql_check_usuario)) {
            mysqli_stmt_bind_param($stmt_check_usuario, "s", $usuario);
            mysqli_stmt_execute($stmt_check_usuario);
            mysqli_stmt_store_result($stmt_check_usuario);
            if (mysqli_stmt_num_rows($stmt_check_usuario) > 0) {
                $erros[] = "Este nome de usuário já está em uso. Escolha outro.";
            }
            mysqli_stmt_close($stmt_check_usuario);
        } else {
            $erros[] = "Erro ao verificar nome de usuário. Tente novamente.";
            error_log("Erro ao preparar statement para verificar usuário: " . mysqli_error($conexao));
        }

        // 6. Verificar se o E-MAIL já existe
        $sql_check_email = "SELECT id FROM usuarios WHERE email = ?";
        if ($stmt_check_email = mysqli_prepare($conexao, $sql_check_email)) {
            mysqli_stmt_bind_param($stmt_check_email, "s", $email);
            mysqli_stmt_execute($stmt_check_email);
            mysqli_stmt_store_result($stmt_check_email);
            if (mysqli_stmt_num_rows($stmt_check_email) > 0) {
                $erros[] = "Este e-mail já está cadastrado. Tente outro ou faça login.";
            }
            mysqli_stmt_close($stmt_check_email);
        } else {
            $erros[] = "Erro ao verificar e-mail. Tente novamente.";
            error_log("Erro ao preparar statement para verificar email: " . mysqli_error($conexao));
        }
    }

    // --- Processamento Final ---
    if (empty($erros)) {
        // Todas as validações passaram! Criptografar a senha
        $senha_hasheada = password_hash($senha, PASSWORD_DEFAULT);

        // Preparar a instrução SQL para inserir o novo usuário
        $sql_insert_usuario = "INSERT INTO usuarios (usuario, email, senha) VALUES (?, ?, ?)";
        if ($stmt_insert_usuario = mysqli_prepare($conexao, $sql_insert_usuario)) {
            mysqli_stmt_bind_param($stmt_insert_usuario, "sss", $usuario, $email, $senha_hasheada);
            
            if (mysqli_stmt_execute($stmt_insert_usuario)) {
                echo "Sucesso! Usuário cadastrado. Você já pode fazer login.";
            } else {
                echo "Erro: Não foi possível realizar o cadastro. Tente novamente mais tarde.";
                error_log("Erro ao executar insert de usuário: " . mysqli_stmt_error($stmt_insert_usuario));
            }
            mysqli_stmt_close($stmt_insert_usuario);
        } else {
            echo "Erro: Falha crítica no servidor ao tentar cadastrar. Tente novamente mais tarde.";
            error_log("Erro ao preparar statement para inserir usuário: " . mysqli_error($conexao));
        }
    } else {
        // Se houver erros de validação, concatenar e exibi-los
        echo "Erro no cadastro:\n- " . implode("\n- ", $erros);
    }

    // Fechar a conexão com o banco de dados
    mysqli_close($conexao);

} else {
    // Se o método não for POST
    echo "Erro: Requisição inválida.";
}
?>