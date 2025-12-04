<?php
session_start();
include 'db-config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success'=>false, 'msg'=>'Não logado']);
    exit;
}
$usuario_id = $_SESSION['user_id'];
$post_id = intval($_POST['post_id'] ?? 0);
if ($post_id <= 0) {
    echo json_encode(['success'=>false, 'msg'=>'Post inválido']);
    exit;
}

// Verifica se já curtiu
$q = $conn->prepare("SELECT id FROM post_likes WHERE post_id = ? AND usuario_id = ?");
$q->bind_param("ii", $post_id, $usuario_id);
$q->execute();
$res = $q->get_result();

if ($res->num_rows > 0) {
    // remover (unlike)
    $row = $res->fetch_assoc();
    $del = $conn->prepare("DELETE FROM post_likes WHERE id = ?");
    $del->bind_param("i", $row['id']);
    $del->execute();
    echo json_encode(['success'=>true, 'action'=>'unliked']);
    exit;
} else {
    // inserir like
    $ins = $conn->prepare("INSERT INTO post_likes (post_id, usuario_id) VALUES (?, ?)");
    $ins->bind_param("ii", $post_id, $usuario_id);
    $ins->execute();
    echo json_encode(['success'=>true, 'action'=>'liked']);
    exit;
}
