<?php
header("Content-Type: application/json");
require "../db-config.php"; // caminho até o db_config.php

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(["erro" => "Método inválido"]);
    exit;
}

// Recebe os dados enviados pelo JS
$data = json_decode(file_get_contents("php://input"), true);

$nome = $data["nome"] ?? null;
$email = $data["email"] ?? null;
$token = $data["token"] ?? null;
$role = $data["role"] ?? null;

if (!$nome || !$email || !$token || !$role) {
    echo json_encode(["erro" => "Dados incompletos"]);
    exit;
}

// Prepara e insere no banco
$sql = $conexao->prepare("INSERT INTO usuarios (nome, email, senha, role) VALUES (?, ?, ?, ?)");
$senha_hash = password_hash($token, PASSWORD_DEFAULT);
$sql->bind_param("ssss", $nome, $email, $senha_hash, $role);

if ($sql->execute()) {
    echo json_encode(["sucesso" => true]);
} else {
    echo json_encode(["erro" => "Erro ao cadastrar usuário"]);
}
?>
