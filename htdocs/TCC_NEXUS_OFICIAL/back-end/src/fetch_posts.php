<?php
session_start();
include 'db-config.php';
header('Content-Type: application/json');

$user_id = $_SESSION['user_id'] ?? 0;

// busca posts (pode limitar / paginar)
$sql = "SELECT p.id, p.usuario_id, p.texto, p.imagem, p.criado_em, u.nome, u.handle, u.foto_perfil,
            (SELECT COUNT(*) FROM post_likes L WHERE L.post_id = p.id) AS likes_count,
            (SELECT COUNT(*) FROM post_comments C WHERE C.post_id = p.id) AS comments_count,
            (SELECT COUNT(*) FROM post_likes L2 WHERE L2.post_id = p.id AND L2.usuario_id = ?) AS liked_by_me
        FROM posts p
        JOIN usuarios u ON u.id = p.usuario_id
        ORDER BY p.id DESC
        LIMIT 50";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();
$posts = [];
while ($row = $res->fetch_assoc()) {
    $posts[] = $row;
}
echo json_encode(['success'=>true, 'posts'=>$posts]);
