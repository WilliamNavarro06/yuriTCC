<?php
$host = "localhost";
$usuario = "root";
$senha = ""; // senha padrão do XAMPP
$banco = "rede_social";
$porta = 3307; // nome do seu banco

$conn = new mysqli($host, $usuario, $senha, $banco, $porta);

// Verifica conexão
if ($conn->connect_error) {
    die("Erro ao conectar ao banco: " . $conn->connect_error);
}
?>
