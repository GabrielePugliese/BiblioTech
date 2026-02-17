<?php
require __DIR__ . "/config.php";
require __DIR__ . "/funzioni.php";

richiedi_login();
richiedi_ruolo('bibliotecario');

$id_prestito = (int)($_POST['id_prestito'] ?? 0);

if ($id_prestito <= 0) {
    header("Location: gestione_restituzioni.php");
    exit;
}

registra_restituzione($pdo, $id_prestito);

header("Location: gestione_restituzioni.php");
exit;
