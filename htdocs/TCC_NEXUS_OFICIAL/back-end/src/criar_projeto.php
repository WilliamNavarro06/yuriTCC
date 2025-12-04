<?php
session_start();
include 'db-config.php';

header('Content-Type: application/json');

$usuario_id = $_SESSION['user_id'] ?? 0;

$titulo = $_POST['titulo'] ?? '';
$descricao = $_POST['descricao'] ?? '';
$tipo = $_POST['tipo'] ?? 'disciplina';
$status = $_POST['status'] ?? 'rascunho';
$curso = $_POST['curso'] ?? '';

if(!$titulo){
    echo json_encode(['success'=>false,'msg'=>'Título obrigatório']);
    exit;
}

$sql = "INSERT INTO projetos (titulo, descricao, tipo, status, curso, lider_id) VALUES (?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sssssi", $titulo, $descricao, $tipo, $status, $curso, $usuario_id);
if($stmt->execute()){
    echo json_encode(['success'=>true,'projeto_id'=>$stmt->insert_id]);
} else {
    echo json_encode(['success'=>false,'msg'=>$stmt->error]);
}
