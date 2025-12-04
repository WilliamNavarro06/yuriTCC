document.getElementById("registerForm").addEventListener("submit", async function (event) {
    event.preventDefault();

    const nome = document.getElementById("nome").value;
    const email = document.getElementById("email").value;
    const token = document.getElementById("Token").value;
    const role = document.getElementById("role").value;

    try {
        const resposta = await fetch("http://localhost/TCC_NEXUS_OFICIAL/back-end/src/api/cadastro.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ nome, email, token, role })
        });

        // Primeiro leia como texto para debug
        const texto = await resposta.text();
        console.log("Resposta do PHP:", texto);

        // Depois parse para JSON
        const dados = JSON.parse(texto);

        if (dados.sucesso) {
            alert("Cadastro realizado com sucesso!");
            window.location.href = "../html/login.html"; 
        } else {
            alert("Erro: " + dados.erro);
        }

    } catch (e) {
        console.error("Erro na requisição ou parse do JSON:", e);
    }
});

