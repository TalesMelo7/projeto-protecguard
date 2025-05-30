// Espera que todo o conteúdo HTML da página seja carregado antes de executar o script
document.addEventListener('DOMContentLoaded', function() {

    const formLogin = document.querySelector('.formulario');
    const feedbackElement = document.getElementById('mensagem-feedback'); // Para exibir mensagens

    if (formLogin) {
        formLogin.addEventListener('submit', function(event) {
            event.preventDefault(); // Impede o envio padrão do formulário

            const formData = new FormData(formLogin);

            // Limpa mensagens de feedback anteriores
            if (feedbackElement) {
                feedbackElement.innerHTML = '';
                feedbackElement.className = ''; // Limpa classes de estilo (sucesso/erro)
            }

            fetch('php/processa_login.php', { // Caminho para seu script PHP
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    // Se a resposta HTTP não for bem-sucedida (ex: 404, 500, ou o 405 que tínhamos antes)
                    throw new Error('Erro na rede ou no servidor: ' + response.statusText + ' (Status: ' + response.status + ')');
                }
                return response.text(); // Lê a resposta do PHP como texto
            })
            .then(data => {
                // 'data' é a resposta em texto do php/processa_login.php
                console.log('Resposta do servidor:', data); // Para debug

                if (feedbackElement) {
                    feedbackElement.textContent = data; // Exibe a mensagem do PHP
                    
                    // Verifica se a resposta indica sucesso no login
                    if (data.toLowerCase().includes("bem-vindo") || data.toLowerCase().includes("login bem-sucedido")) {
                        feedbackElement.classList.add('sucesso'); // Aplica estilo de sucesso

                        // --- REDIRECIONAMENTO APÓS SUCESSO ---
                        // Mostra a mensagem de sucesso por um breve período e depois redireciona.
                        // Você pode ajustar o tempo (em milissegundos) ou remover o setTimeout
                        // para redirecionar imediatamente.
                        setTimeout(function() {
                            // MUDE 'pagina_principal_logada.html' PARA A PÁGINA DE DESTINO REAL
                            window.location.href = 'index.php'; 
                        }, 1500); // Redireciona após 1.5 segundos (1500 ms)

                    } else {
                        // Se não for sucesso, aplica estilo de erro
                        feedbackElement.classList.add('erro');
                    }
                } else {
                    // Fallback se o elemento de feedback não existir na página HTML
                    alert(data); 
                    if (data.toLowerCase().includes("bem-vindo") || data.toLowerCase().includes("login bem-sucedido")) {
                        // Redirecionamento mesmo se o feedbackElement não existir, mas após o alert
                         setTimeout(function() {
                            window.location.href = 'index.php'; // MUDE PARA A SUA PÁGINA DE DESTINO REAL
                        }, 500); // Tempo menor pois o alert já pausa
                    }
                }
            })
            .catch(error => {
                // Captura erros de rede ou os erros lançados pelo 'throw new Error' acima
                console.error('Erro no processo de fetch:', error);
                const mensagemErroCompleta = 'Ocorreu um erro ao tentar fazer login: ' + error.message;
                
                if (feedbackElement) {
                    feedbackElement.textContent = mensagemErroCompleta;
                    feedbackElement.classList.add('erro');
                } else {
                    alert(mensagemErroCompleta);
                }
            });
        });
    } else {
        console.warn("AVISO: Formulário com a classe '.formulario' não foi encontrado no HTML.");
    }
});