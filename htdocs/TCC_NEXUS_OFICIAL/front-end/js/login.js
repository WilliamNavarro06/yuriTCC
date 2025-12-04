
async function handleLogin(event) {
    // Previne o comportamento padrão do formulário (recarregar a página)
    event.preventDefault(); 

    // --- 1. Coleta e Validação dos Dados ---
    const emailInput = document.getElementById('email')?.value.trim(); 
    // Certifique-se de que este ID ('Token') está correto para o campo de SENHA no seu HTML
    const senhaInput = document.getElementById('Token')?.value.trim(); 

    // Validação básica
    if (!emailInput || !senhaInput) {
        alert('Por favor, preencha o e-mail e a senha.');
        console.warn('Tentativa de login com campos vazios.');
        return; // Sai da função se os dados estiverem incompletos
    }

    const dadosLogin = {
        email: emailInput,
        senha: senhaInput // O PHP deve receber 'senha'
    };

    // --- 2. Realização da Requisição Fetch ---
    const urlLogin = 'http://localhost/TCC_NEXUS_OFICIAL/back-end/src/api/login.php'; 

    try {
        const response = await fetch(urlLogin, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json' // Informa ao servidor que o corpo é JSON
            },
            body: JSON.stringify(dadosLogin) // Converte o objeto JS para JSON
        });

        // --- 3. Processamento da Resposta ---
        const resultado = await response.json();

        if (response.ok) { // Status 200-299: Sucesso no Login
            
            console.log('Login efetuado com sucesso!', resultado);
            alert(`Bem-vindo(a), ${resultado.usuario.nome}!`);
            
            // 🎯 REDIRECIONAMENTO APÓS O LOGIN BEM-SUCEDIDO
            window.location.href = 'feed.html'; // Redireciona para a página do feed

        } else { 
            // Status fora da faixa 200-299 (e.g., 401 Não Autorizado, 400 Requisição Inválida)
            
            const errorMessage = resultado.erro || 'Ocorreu um erro desconhecido.';
            console.error(`Erro de Login (${response.status}):`, errorMessage);
            alert(`Falha no Login: ${errorMessage}`);
        }

    } catch (error) {
        // Erro de Rede ou JSON inválido
        console.error('Erro Fatal na Requisição:', error);
        alert('Não foi possível conectar ao servidor ou houve um erro de rede. Tente novamente.');
    }
}

// --- 4. Configuração do Listener de Evento ---
const formLogin = document.getElementById('loginForm'); 
if (formLogin) {
    formLogin.addEventListener('submit', handleLogin);
} else {
    // Alerta no console se o ID do formulário estiver incorreto
    console.error("ERRO: Elemento com ID 'loginForm' não encontrado. Verifique seu HTML.");
}