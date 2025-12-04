<?php
session_start();
include ' db-config.php';

header('Content-Type: application/json');

$sql = "SELECT a.*, u.nome, u.handle
        FROM atividade_projeto a
        JOIN usuarios u ON u.id = a.usuario_id
        ORDER BY a.criado_em DESC
        LIMIT 20";

$res = $conn->query($sql);

$atividades = [];
while($row = $res->fetch_assoc()){
    $atividades[] = [
        'id'=>$row['id'],
        'usuario_id'=>$row['usuario_id'],
        'nome'=>$row['nome'],
        'handle'=>$row['handle'],
        'acao'=>$row['acao'],
        'criado_em'=>date('d/m/Y H:i', strtotime($row['criado_em']))
    ];
}

echo json_encode(['success'=>true,'atividades'=>$atividades]);
