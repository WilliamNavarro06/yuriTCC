<?php
session_start();
include '../../back-end/src/db-config.php'; // Conexão com banco

// Verifica se veio o parâmetro "u"
if (!isset($_GET['u']) || empty($_GET['u'])) {
    die("Erro: nenhum perfil especificado.");
}

// Obtém o handle enviado
$handle = $_GET['u'];

// Remove o @ caso tenha
$handle = ltrim($handle, '@');

// Consulta no banco
$sql = "SELECT id, nome, email, handle, role FROM usuarios WHERE handle = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $handle);
$stmt->execute();
$result = $stmt->get_result();

// Se não encontrou usuário
if ($result->num_rows === 0) {
    die("Perfil não encontrado!");
}

$usuario = $result->fetch_assoc();

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Perfil de <?= htmlspecialchars($usuario['nome']) ?></title>
    <link rel="stylesheet" href="../css/perfil.css">
</head>

<body>

    <div class="perfil-container">

        <h1>@<?= htmlspecialchars($usuario['handle']) ?></h1>

        <p><strong>Nome:</strong> <?= htmlspecialchars($usuario['nome']) ?></p>
        <p><strong>E-mail:</strong> <?= htmlspecialchars($usuario['email']) ?></p>
        <p><strong>Perfil:</strong> <?= htmlspecialchars($usuario['role']) ?></p>

        <a href="feed.php">Voltar ao Feed</a>

    </div>

</body>
</html>
