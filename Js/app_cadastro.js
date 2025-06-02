document.addEventListener('DOMContentLoaded', function() {
    const formCadastro = document.getElementById('form-cadastro'); // Usando o ID do formulário
    const feedbackElementCadastro = document.getElementById('mensagem-feedback-cadastro');

    if (formCadastro) {
        formCadastro.addEventListener('submit', function(event) {
            event.preventDefault(); // Impede o envio padrão do formulário

            const formData = new FormData(formCadastro);
            const senha = formData.get('senha');
            const confirmarSenha = formData.get('confirmar_senha');

            // Limpa mensagens de feedback anteriores
            if (feedbackElementCadastro) {
                feedbackElementCadastro.innerHTML = '';
                feedbackElementCadastro.className = ''; // Limpa classes de estilo
            }

            // Validação básica no lado do cliente (Client-side)
            if (senha !== confirmarSenha) {
                if (feedbackElementCadastro) {
                    feedbackElementCadastro.textContent = 'Erro: As senhas não coincidem!';
                    feedbackElementCadastro.classList.add('erro');
                } else {
                    alert('Erro: As senhas não coincidem!');
                }
                return; // Interrompe o envio se as senhas não baterem
            }
            
            // Adicione aqui mais validações de cliente se desejar (ex: força da senha, formato do email)

            // Envia os dados para o script PHP de processamento do cadastro
            fetch('php/processa_cadastro.php', { // Script PHP que vamos criar a seguir
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Erro na rede ou no servidor: ' + response.statusText + ' (Status: ' + response.status + ')');
                }
                return response.text(); // Esperamos texto como resposta do PHP
            })
            .then(data => {
                console.log('Resposta do servidor (cadastro):', data); // Para debug

                if (feedbackElementCadastro) {
                    feedbackElementCadastro.textContent = data; // Exibe a mensagem do PHP
                    if (data.toLowerCase().includes("sucesso")) {
                        feedbackElementCadastro.classList.add('sucesso');
                        formCadastro.reset(); // Limpa o formulário após o sucesso
                        // Opcional: Redirecionar para a página de login ou mostrar uma mensagem mais elaborada
                        // setTimeout(function() { window.location.href = 'login.html'; }, 2000);
                    } else {
                        feedbackElementCadastro.classList.add('erro');
                    }
                } else {
                    alert(data);
                    if (data.toLowerCase().includes("sucesso")) {
                        formCadastro.reset(); // Limpa o formulário
                    }
                }
            })
            .catch(error => {
                console.error('Erro no processo de cadastro via fetch:', error);
                const mensagemErroCompleta = 'Ocorreu um erro ao tentar cadastrar: ' + error.message;
                if (feedbackElementCadastro) {
                    feedbackElementCadastro.textContent = mensagemErroCompleta;
                    feedbackElementCadastro.classList.add('erro');
                } else {
                    alert(mensagemErroCompleta);
                }
            });
        });
    } else {
        console.warn("AVISO: Formulário com id 'form-cadastro' não foi encontrado no HTML.");
    }
});