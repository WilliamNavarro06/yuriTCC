<?php
session_start();
include 'db-config.php'; // ajustar caminho conforme seu projeto

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../front-end/html/login.php");
    exit;
}

$usuario_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../../front-end/html/feed.php");
    exit;
}

$texto = trim($_POST['texto'] ?? '');
$imagem_nome = null;

// UPLOAD: validações
if (!empty($_FILES['imagem']['name'])) {
    $allowed = ['jpg','jpeg','png','gif','webp'];
    $maxBytes = 3 * 1024 * 1024; // 3MB limite

    $origName = $_FILES['imagem']['name'];
    $tmp = $_FILES['imagem']['tmp_name'];
    $size = $_FILES['imagem']['size'];
    $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));

    if (!in_array($ext, $allowed)) {
        die("Tipo de imagem não permitido. Tipos: " . implode(', ', $allowed));
    }
    if ($size > $maxBytes) {
        die("Imagem muito grande. Tamanho máximo: 3MB");
    }

    $uploadDir = "../../front-end/uploads/";
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

    // gerar nome único + preservar extensão
    $imagem_nome = uniqid("img_") . "." . $ext;
    $dest = $uploadDir . $imagem_nome;

    if (!move_uploaded_file($tmp, $dest)) {
        die("Falha ao mover arquivo.");
    }

    // opcional: reduzir imagem (não implementado aqui — pode adicionar GD/Imagick)
}

// INSERE NO BD (verifique colunas: usuário, coluna de texto pode se chamar 'texto' ou 'conteudo' -> ajustar)
$sql = "INSERT INTO posts (usuario_id, texto, imagem, criado_em) VALUES (?, ?, ?, NOW())";
$stmt = $conn->prepare($sql);
if (!$stmt) die("Erro prepare: " . $conn->error);
$stmt->bind_param("iss", $usuario_id, $texto, $imagem_nome);

if ($stmt->execute()) {
    // para AJAX: retornar JSON
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
        echo json_encode(['success' => true]);
        exit;
    }
    header("Location: ../../front-end/html/feed.php");
    exit;
} else {
    die("Erro ao postar: " . $conn->error);
}
