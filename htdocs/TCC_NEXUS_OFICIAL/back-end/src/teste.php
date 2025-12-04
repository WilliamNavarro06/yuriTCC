<?php
require "db-config.php";

if ($conexao->connect_error) {
    echo "Erro: " . $conexao->connect_error;
} else {
    echo "Conexão OK!";
}
