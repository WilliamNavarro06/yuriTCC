<?php
if (session_status() === PHP_SESSION_NONE) session_start();
include '../../back-end/src/db-config.php';
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }
$viewer_id = $_SESSION['user_id'];

// pega id do perfil via ?u=handle ou ?id=#
$handle = $_GET['u'] ?? null;
$profile_id = intval($_GET['id'] ?? 0);

if ($handle) {
    $stmt = $conn->prepare("SELECT * FROM usuarios WHERE handle = ?");
    $stmt->bind_param("s", $handle);
} else {
    $stmt = $conn->prepare("SELECT * FROM usuarios WHERE id = ?");
    $stmt->bind_param("i", $profile_id);
}
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
if (!$user) { echo "Usuário não encontrado"; exit; }
?>

<!doctype html>
<html lang="pt-br">
<head>
<meta charset="utf-8">
<title>Perfil de <?=htmlspecialchars($user['nome'])?></title>
<link rel="stylesheet" href="../css/feed.css">
</head>
<body>
<div class="profile-header">
    <img src="<?= $user['foto_perfil'] ? '../uploads/' . htmlspecialchars($user['foto_perfil']) : '../images/default-avatar.png' ?>" width="120" height="120" style="border-radius:50%;">
    <h1><?= htmlspecialchars($user['nome']) ?></h1>
    <p>@<?= htmlspecialchars($user['handle']) ?></p>
    <p><?= htmlspecialchars($user['bio'] ?? '') ?></p>
</div>

<div class="user-posts">
    <h2>Posts</h2>
    <?php
    $q = $conn->prepare("SELECT p.*, (SELECT COUNT(*) FROM post_likes L WHERE L.post_id = p.id) AS likes_count FROM posts p WHERE p.usuario_id = ? ORDER BY p.id DESC");
    $q->bind_param("i", $user['id']);
    $q->execute();
    $res = $q->get_result();
    while ($p = $res->fetch_assoc()) {
        echo '<div class="postagem">';
        echo '<div class="conteudo-post"><p>'.nl2br(htmlspecialchars($p['texto'])).'</p>';
        if (!empty($p['imagem'])) echo '<img src="../uploads/'.htmlspecialchars($p['imagem']).'" class="post-img">';
        echo '</div>';
        echo '<div class="interacao-post"><span class="interacao-item"><i class="fas fa-thumbs-up"></i> '.$p['likes_count'].'</span></div>';
        echo '</div>';
    }
    ?>
</div>
</body>
</html>
