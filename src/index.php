<?php
require __DIR__ . "/config.php";

if (!empty($_SESSION['autenticato'])) {
    if (($_SESSION['ruolo'] ?? '') === 'bibliotecario') {
        header("Location: gestione_restituzioni.php");
    } else {
        header("Location: libri.php");
    }
    exit;
}

header("Location: login.php");
exit;
