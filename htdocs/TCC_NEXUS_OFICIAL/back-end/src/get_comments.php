<?php
session_start();
include 'db-config.php';
header('Content-Type: application/json');

$post_id = intval($_GET['post_id'] ?? 0);
if ($post_id <= 0) { echo json_encode(['success'=>false]); exit; }

$stmt = $conn->prepare("SELECT c.id, c.conteudo, c.criado_em, u.nome FROM post_comments c JOIN usuarios u ON u.id = c.usuario_id WHERE c.post_id = ? ORDER BY c.id DESC");
$stmt->bind_param("i", $post_id);
$stmt->execute();
$res = $stmt->get_result();
$comments = [];
while ($r = $res->fetch_assoc()) $comments[] = $r;

echo json_encode(['success'=>true, 'comments'=>$comments]);
