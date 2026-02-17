<?php
require __DIR__ . "/config.php";
require __DIR__ . "/funzioni.php";

richiedi_login();
richiedi_ruolo('studente');

$id_utente = (int)$_SESSION['id_utente'];
$id_libro = (int)($_POST['id_libro'] ?? 0);

if ($id_libro <= 0) {
    header("Location: libri.php");
    exit;
}

$ok = effettua_prestito($pdo, $id_utente, $id_libro);

if ($ok) {
    header("Location: prestiti.php");
} else {
    header("Location: libri.php");
}
exit;
