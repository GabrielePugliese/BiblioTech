<?php
require "config.php";
require "funzioni.php";

if (($_SESSION['stato_login'] ?? '') !== 'OTP_IN_ATTESA') {
    header("Location: login.php");
    exit;
}

$id_utente = (int)$_SESSION['id_utente_otp'];
$errore = "";
$msg = "";

if (isset($_POST['rinvia'])) {

    $email = $pdo->prepare("SELECT email FROM utente WHERE id_utente=?");
    $email->execute([$id_utente]);
    $mail = $email->fetchColumn();

    $codice = crea_otp($pdo, $id_utente);
    invia_email_otp($mail, $codice);

    $_SESSION['scadenza_otp_sessione'] = time() + 120;
    $msg = "Nuovo codice inviato";
}

if (isset($_POST['verifica'])) {

    $codice = trim($_POST['codice'] ?? '');

    if ($codice === '') {
        $errore = "Inserisci il codice";
    }
    elseif (time() > $_SESSION['scadenza_otp_sessione']) {
        $errore = "Codice scaduto";
    }
    elseif (verifica_otp($pdo, $id_utente, $codice)) {

        $u = $pdo->prepare("SELECT id_utente, username, ruolo FROM utente WHERE id_utente=?");
        $u->execute([$id_utente]);
        $u = $u->fetch();

        elimina_otp($pdo, $id_utente);

        unset($_SESSION['stato_login'], $_SESSION['id_utente_otp'], $_SESSION['scadenza_otp_sessione']);

        $_SESSION['autenticato'] = true;
        $_SESSION['id_utente'] = $u['id_utente'];
        $_SESSION['username'] = $u['username'];
        $_SESSION['ruolo'] = $u['ruolo'];

        rigenera_sessione();

        echo "LOGIN COMPLETATO";
        exit;
    }
    else {
        $errore = "Codice errato";
    }
}

require "header.php";
?>

<h1>Inserisci OTP</h1>

<?php if ($errore): ?><div class="msg errore"><?= $errore ?></div><?php endif; ?>
<?php if ($msg): ?><div class="msg"><?= $msg ?></div><?php endif; ?>

<form method="post">
<input name="codice" placeholder="Codice OTP">
<button name="verifica">Verifica</button>
</form>

<form method="post">
<button name="rinvia">Rinvia codice</button>
</form>

<?php require "footer.php"; ?>
