<?php
declare(strict_types=1);

session_start();

$nome_db = getenv('DB_NAME') ?: 'bibliotech';
$utente_db = getenv('DB_USER') ?: 'app';
$password_db = getenv('DB_PASS') ?: 'apppass';
$host_db = getenv('DB_HOST') ?: 'db';

$dsn = "mysql:host=$host_db;dbname=$nome_db;charset=utf8mb4";

try {
    $pdo = new PDO($dsn, $utente_db, $password_db, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    die("Errore: impossibile collegarsi al database.");
}
