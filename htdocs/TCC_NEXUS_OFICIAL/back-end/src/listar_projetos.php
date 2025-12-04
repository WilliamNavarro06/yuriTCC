<?php
session_start();
include 'db-config.php';

header('Content-Type: application/json');

$usuario_id = $_SESSION['user_id'] ?? 0;
$tipo   = $_GET['tipo'] ?? '';
$status = $_GET['status'] ?? '';
$search = $_GET['search'] ?? '';

$where = [];
$params = [];
$types = '';

if($tipo){
    $where[] = 'p.tipo=?';
    $types .= 's';
    $params[] = $tipo;
}
if($status){
    $where[] = 'p.status=?';
    $types .= 's';
    $params[] = $status;
}
if($search){
    $where[] = '(p.titulo LIKE ? OR p.descricao LIKE ?)';
    $types .= 'ss';
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$whereSql = $where ? 'WHERE '.implode(' AND ', $where) : '';

$sql = "SELECT p.*, u.nome as lider_nome 
        FROM projetos p
        JOIN usuarios u ON u.id = p.lider_id
        $whereSql
        ORDER BY p.criado_em DESC";

$stmt = $conn->prepare($sql);
if($params){
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$res = $stmt->get_result();

$projetos = [];
while($row = $res->fetch_assoc()){
    $projetos[] = $row;
}

echo json_encode(['success'=>true,'projetos'=>$projetos]);
