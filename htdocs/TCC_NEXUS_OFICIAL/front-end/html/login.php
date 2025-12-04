<?php
session_start();
include '../../back-end/src/db-config.php'; // Conexão

// Se já estiver logado, vai direto para o feed
if (isset($_SESSION['user_id'])) {
    header("Location: feed.php");
    exit;
}

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $login = $_POST['login'];        // email ou handle
    $password = $_POST['password'];  // senha digitada

    // Busca o usuário pelo email OU pelo handle
    $sql = "SELECT * FROM usuarios WHERE email = ? OR handle = ?";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        die("Erro na preparação da query: " . $conn->error);
    }

    $stmt->bind_param("ss", $login, $login);
    $stmt->execute();
    $result = $stmt->get_result();

    // Usuário encontrado?
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // Verifica a senha criptografada
        if (password_verify($password, $user['senha'])) {

            // Salva dados da sessão
            $_SESSION['user_id']      = $user['id'];
            $_SESSION['nome']         = $user['nome'];
            $_SESSION['handle']       = $user['handle'];
            $_SESSION['email']        = $user['email'];
            $_SESSION['bio']          = $user['bio'] ?? "";
            $_SESSION['foto']         = $user['foto_perfil'] ?? "";
            $_SESSION['seguidores']   = $user['seguidores'] ?? 0;
            $_SESSION['seguindo']     = $user['seguindo'] ?? 0;
            $_SESSION['projetos']     = $user['projetos'] ?? 0;

            header("Location: feed.php");
            exit;

        } else {
            $error = "Senha incorreta.";
        }

    } else {
        $error = "Usuário não encontrado.";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Nexus | Login</title>
<link rel="icon" href="../images/logo_botinho_pagina.png" type="image/x-icon">
<link rel="stylesheet" href="../css/login.css">
<link href="https://fonts.googleapis.com/css2?family=Montserrat&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>

<body>
<div class="login-container">
    <form class="login-card" method="POST">
        <h1 class="login-heading">Bem-vindo ao Nexus</h1>
        <img id="logoOFC" src="../images/logo_oficial.png"/>
        <p class="login-subheading">Entre na sua conta para continuar</p>

        <div class="input-field-group">
            <i class="fas fa-envelope input-icon"></i>
            <input type="text" name="login" class="input-field" placeholder="E-mail ou Usuário" required>
        </div>

        <div class="input-field-group">
            <i class="fas fa-lock input-icon"></i>
            <input type="password" name="password" class="input-field" placeholder="Senha" required>
        </div>

        <?php if(!empty($error)) echo "<p style='color:red; text-align:center;'>$error</p>"; ?>

        <div class="login-buttons">
            <button type="submit" class="primary-btn">Login</button>
            <a href="cadastro.php" class="secondary-btn">Cadastrar</a>
        </div>

        <a href="esqueci-senha.php" class="forgot-password-link">Esqueci minha senha</a>
    </form>
</div>

</body>
</html>
