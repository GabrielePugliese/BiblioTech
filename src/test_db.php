<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

echo "1) PHP OK<br>";

require __DIR__ . "/config.php";
echo "2) config.php caricato<br>";

echo "3) PDO class: " . (class_exists("PDO") ? "SI" : "NO") . "<br>";

try {
    $ris = $pdo->query("SELECT COUNT(*) AS tot FROM libro")->fetch();
    echo "4) DB OK - Libri: " . $ris["tot"];
} catch (Throwable $e) {
    echo "ERRORE QUERY: " . htmlspecialchars($e->getMessage());
}
