<?php
// ============================================================
// CONEXÃO COM O BANCO DE DADOS (MySQL / XAMPP)
// ============================================================

$host    = "localhost";
$banco   = "control_estoque";
$usuario = "root";
$senha   = "";

try {
    // Criação da conexão usando PDO
    $pdo = new PDO("mysql:host=$host;dbname=$banco;charset=utf8", $usuario, $senha);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro ao conectar com o banco de dados: " . $e->getMessage());
}
?>
