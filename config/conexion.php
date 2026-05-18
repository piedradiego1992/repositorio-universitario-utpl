<?php
$host = "localhost";
$db   = "sistema_universitario";
$user = "root";
$pass = ""; // Coloca tu contraseña de MySQL si configuraste una

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error crítico de conexión: " . $e->getMessage());
}
?>