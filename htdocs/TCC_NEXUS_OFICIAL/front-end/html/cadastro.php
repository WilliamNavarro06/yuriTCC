<?php
session_start();
include '../../back-end/src/db-config.php'; // Conexão

// Função para gerar handle único
function gerarHandle($nome, $conn) {

    // 1. Normaliza o nome (tira acentos e caracteres inválidos)
    $base = strtolower($nome);
    $base = iconv('UTF-8', 'ASCII//TRANSLIT', $base);
    $base = preg_replace('/[^a-z0-9]/', '', $base);

    // Caso o nome seja muito curto
    if (strlen($base) < 3) {
        $base = "user";
    }

    // 2. Criar um sufixo mais profissional
    $sufixo = substr(str_shuffle("abcdefghijklmnopqrstuvwxyz"), 0, 2)
            . rand(10, 99)
            . substr(str_shuffle("abcdefghijklmnopqrstuvwxyz0123456789"), 0, 2);

    $handle = $base . "_" . $sufixo;

    // 3. Verificação no banco (repete até encontrar um único)
    $sql = "SELECT id FROM usuarios WHERE handle = ?";
    $stmt = $conn->prepare($sql);

    while (true) {
        $stmt->bind_param("s", $handle);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows === 0) { 
            break; 
        }

        $sufixo = substr(str_shuffle("abcdefghijklmnopqrstuvwxyz"), 0, 2)
                . rand(10, 99)
                . substr(str_shuffle("abcdefghijklmnopqrstuvwxyz0123456789"), 0, 2);

        $handle = $base . "_" . $sufixo;
    }

    return $handle;
}


// Processar cadastro
$erro = "";
$sucesso = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nome   = $_POST['nome'];
    $email  = $_POST['email'];
    $token  = $_POST['token'];
    $role   = $_POST['role'];

    if (empty($nome) || empty($email) || empty($token) || empty($role)) {
        $erro = "Preencha todos os campos.";
    } else {

        $senhaCripto = password_hash($token, PASSWORD_DEFAULT);
        $handle = gerarHandle($nome, $conn);

        $sql = "INSERT INTO usuarios (nome, email, senha, handle, role)
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssss", $nome, $email, $senhaCripto, $handle, $role);

        if ($stmt->execute()) {

            // Mensagem de sucesso
            $sucesso = "Cadastro realizado com sucesso! Faça login.";

            // **OPÇÃO 1 — destruir sessão após cadastro**
            session_unset();
            session_destroy();

        } else {
            $erro = "Erro ao cadastrar. Verifique os dados.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nexus | Cadastro</title>

    <link rel="icon" href="../images/logo_botinho_pagina.png" type="image/x-icon">
    <link rel="stylesheet" href="../css/login.css"> 

    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>
    <div class="login-container">

        <form class="login-card" method="POST">
            <h1 class="login-heading">Cadastre-se no</h1>
            <img src="../images/logo_oficial.png" style="width: 150px;">
            <p class="login-subheading">Crie sua conta para começar</p>

            <!-- Nome -->
            <div class="input-field-group">
                <i class="fas fa-user input-icon"></i>
                <input type="text" class="input-field" name="nome" placeholder="Nome Completo" required>
            </div>

            <!-- Perfil (Aluno ou Professor) -->
            <div class="input-field-group">
                <i class="fas fa-users input-icon"></i>
                <select class="input-field" name="role" required>
                    <option value="">Selecione seu perfil</option>
                    <option value="aluno">Aluno</option>
                    <option value="professor">Professor</option>
                </select>
            </div>

            <!-- Email -->
            <div class="input-field-group">
                <i class="fas fa-at input-icon"></i>
                <input type="email" class="input-field" name="email" placeholder="E-mail Institucional" required>
            </div>

            <!-- Token / Senha -->
            <div class="input-field-group">
                <i class="fas fa-lock input-icon"></i>
                <input type="password" class="input-field" name="token" placeholder="Token" required>
            </div>

            <!-- Mensagens -->
            <?php if (!empty($erro)) { ?>
                <p style="color:red; text-align:center;"><?= $erro ?></p>
            <?php } ?>

            <?php if (!empty($sucesso)) { ?>
                <p style="color:green; text-align:center;"><?= $sucesso ?></p>
            <?php } ?>

            <!-- Botões -->
            <div class="login-buttons">
                <button type="submit" class="primary-btn">Cadastrar</button>
                <a href="login.php" class="secondary-btn">Voltar para Login</a>
            </div>
        </form>

    </div>
</body>
</html>
