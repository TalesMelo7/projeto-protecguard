document.addEventListener('DOMContentLoaded', function() {
    const formRecuperar = document.getElementById('form-recuperar-senha');
    const feedbackElementRecuperar = document.getElementById('mensagem-feedback-recuperar');

    if (formRecuperar) {
        formRecuperar.addEventListener('submit', function(event) {
            event.preventDefault();
            const formData = new FormData(formRecuperar);

            if (feedbackElementRecuperar) {
                feedbackElementRecuperar.innerHTML = '';
                feedbackElementRecuperar.className = '';
                feedbackElementRecuperar.textContent = 'Processando sua solicitação...';
            }

            fetch('php/solicitar_recuperacao.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    // Tenta pegar o texto do erro do servidor se possível, ou usa o statusText padrão
                    return response.text().then(text => {
                        throw new Error('Erro na rede ou no servidor: ' + (text || response.statusText) + ' (Status: ' + response.status + ')');
                    });
                }
                return response.json(); // <<< MUDANÇA: Espera JSON agora
            })
            .then(data => { // 'data' agora é um objeto JavaScript
                console.log('Resposta do servidor (JSON):', data);

                if (feedbackElementRecuperar) {
                    let htmlConteudo = '';
                    // Mensagem principal para o usuário
                    htmlConteudo += '<p>' + data.mensagem.replace(/\n/g, '<br>') + '</p>';

                    // Se o link de debug estiver presente (significa que um token foi gerado)
                    if (data.link_debug) {
                        htmlConteudo += '<div style="margin-top: 15px; padding: 10px; border: 1px solid #ccc; background-color: #f9f9f9; color: #333;">';
                        htmlConteudo += 'Link para redefinição:<br>';
                        htmlConteudo += '<a href="' + data.link_debug + '" target="_blank" style="word-break: break-all;">' + data.link_debug + '</a><br>';
                        htmlConteudo += '</div>';
                    }
                    
                    feedbackElementRecuperar.innerHTML = htmlConteudo; // Usa innerHTML para renderizar o link

                    if (data.status === 'sucesso' || data.status === 'sucesso_sem_match') {
                        feedbackElementRecuperar.classList.add('sucesso');
                    } else {
                        feedbackElementRecuperar.classList.add('erro');
                    }
                } else {
                    // Fallback para alert, menos ideal para HTML formatado
                    let alertMessage = data.mensagem;
                    if (data.link_debug) {
                        alertMessage += "\n\nLink de Debug: " + data.link_debug;
                    }
                    alert(alertMessage);
                }
            })
            .catch(error => {
                console.error('Erro no processo de recuperar senha via fetch:', error);
                const mensagemErroCompleta = 'Ocorreu um erro: ' + error.message;
                if (feedbackElementRecuperar) {
                    feedbackElementRecuperar.textContent = mensagemErroCompleta;
                    feedbackElementRecuperar.classList.add('erro');
                } else {
                    alert(mensagemErroCompleta);
                }
            });
        });
    } else {
        console.warn("AVISO: Formulário com id 'form-recuperar-senha' não foi encontrado.");
    }
});