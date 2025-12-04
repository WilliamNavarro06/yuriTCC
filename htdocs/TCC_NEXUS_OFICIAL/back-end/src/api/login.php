<?php
header("Content-Type: application/json");
require "../db-config.php"; // Caminho para o db_config.php

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    // Responde com erro se não for uma requisição POST
    http_response_code(405); // Método Não Permitido
    echo json_encode(["erro" => "Método inválido"]);
    exit;
}

// 1. Recebe os dados de login (e-mail e senha)
$data = json_decode(file_get_contents("php://input"), true);

$email = $data["email"] ?? null;
$senha_fornecida = $data["senha"] ?? null; // Altere para "senha", não "token"

if (!$email || !$senha_fornecida) {
    // Responde com erro se os dados estiverem incompletos
    http_response_code(400); // Requisição Inválida
    echo json_encode(["erro" => "E-mail e senha são obrigatórios"]);
    exit;
}

// 2. Busca o usuário no banco de dados pelo e-mail
// Usa prepared statement para prevenir injeção de SQL
$sql = $conexao->prepare("SELECT id, nome, senha, role FROM usuarios WHERE email = ?");
$sql->bind_param("s", $email);
$sql->execute();
$resultado = $sql->get_result();
$usuario = $resultado->fetch_assoc();

if (!$usuario) {
    // Usuário não encontrado
    http_response_code(401); // Não Autorizado
    echo json_encode(["erro" => "E-mail ou senha incorretos"]);
    exit;
}

// 3. Verifica se a senha fornecida corresponde à senha hash no banco
$senha_hash_no_banco = $usuario["senha"];

if (password_verify($senha_fornecida, $senha_hash_no_banco)) {
    // Senha correta: Login bem-sucedido
    
    // **INICIAR SESSÃO AQUI**
    // (Em uma aplicação real, você iniciaria uma sessão ou geraria um JWT para manter o usuário logado)
    
    // Exemplo de resposta de sucesso:
    http_response_code(200); // OK
    echo json_encode([
        "sucesso" => true,
        "mensagem" => "Login realizado com sucesso",
        "usuario" => [
            "id" => $usuario["id"],
            "nome" => $usuario["nome"],
            "role" => $usuario["role"]
        ]
    ]);
} else {
    // Senha incorreta
    http_response_code(401); // Não Autorizado
    echo json_encode(["erro" => "E-mail ou senha incorretos"]);
}

$sql->close();
$conexao->close();
?>