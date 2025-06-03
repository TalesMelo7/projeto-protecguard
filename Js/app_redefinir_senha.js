document.addEventListener('DOMContentLoaded', function() {
    const formRedefinirSenha = document.getElementById('form-redefinir-senha');
    const feedbackElementRedefinir = document.getElementById('mensagem-feedback-redefinir');

    if (formRedefinirSenha) {
        formRedefinirSenha.addEventListener('submit', function(event) {
            event.preventDefault(); // Impede o envio padrão do formulário

            const formData = new FormData(formRedefinirSenha);
            const novaSenha = formData.get('nova_senha');
            const confirmarNovaSenha = formData.get('confirmar_nova_senha');

            // Limpa mensagens de feedback anteriores
            if (feedbackElementRedefinir) {
                feedbackElementRedefinir.innerHTML = '';
                feedbackElementRedefinir.className = ''; // Limpa classes de estilo
            }

            // Validação básica no lado do cliente
            if (!novaSenha || !confirmarNovaSenha) {
                const msg = 'Por favor, preencha ambos os campos de senha.';
                if (feedbackElementRedefinir) {
                    feedbackElementRedefinir.textContent = msg;
                    feedbackElementRedefinir.classList.add('erro');
                } else {
                    alert(msg);
                }
                return;
            }

            if (novaSenha !== confirmarNovaSenha) {
                const msg = 'Erro: As novas senhas não coincidem!';
                if (feedbackElementRedefinir) {
                    feedbackElementRedefinir.textContent = msg;
                    feedbackElementRedefinir.classList.add('erro');
                } else {
                    alert(msg);
                }
                return; // Interrompe o envio
            }

            // Envia os dados para o script PHP que processará a alteração da senha
            fetch('php/processa_nova_senha.php', { // Novo script PHP que vamos criar
                method: 'POST',
                body: formData // formData já contém email e token dos campos ocultos
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Erro na rede ou no servidor: ' + response.statusText + ' (Status: ' + response.status + ')');
                }
                return response.text();
            })
            .then(data => {
                console.log('Resposta do servidor (redefinir senha):', data);

                if (feedbackElementRedefinir) {
                    feedbackElementRedefinir.textContent = data;
                    if (data.toLowerCase().includes("sucesso") || data.toLowerCase().includes("senha redefinida")) {
                        feedbackElementRedefinir.classList.add('sucesso');
                        formRedefinirSenha.reset(); // Limpa o formulário
                        // Opcional: Desabilitar o formulário ou redirecionar para o login após um tempo
                        // setTimeout(function() { window.location.href = 'login.html'; }, 3000);
                    } else {
                        feedbackElementRedefinir.classList.add('erro');
                    }
                } else {
                    alert(data);
                     if (data.toLowerCase().includes("sucesso") || data.toLowerCase().includes("senha redefinida")) {
                        formRedefinirSenha.reset();
                    }
                }
            })
            .catch(error => {
                console.error('Erro no processo de redefinir senha via fetch:', error);
                const mensagemErroCompleta = 'Ocorreu um erro ao tentar redefinir a senha: ' + error.message;
                if (feedbackElementRedefinir) {
                    feedbackElementRedefinir.textContent = mensagemErroCompleta;
                    feedbackElementRedefinir.classList.add('erro');
                } else {
                    alert(mensagemErroCompleta);
                }
            });
        });
    } else {
        // Isso não deve acontecer se o script for chamado pela página redefinir_senha.php correta
        console.warn("AVISO: Formulário com id 'form-redefinir-senha' não foi encontrado no HTML.");
    }
});