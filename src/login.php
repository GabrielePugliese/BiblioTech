<?php
declare(strict_types=1);

require __DIR__ . "/config.php";
require __DIR__ . "/funzioni.php";

$errore = "";
$info = "";

// Se già loggato, manda alla pagina giusta
if (!empty($_SESSION['autenticato']) && ($_SESSION['autenticato'] === true)) {
    if (($_SESSION['ruolo'] ?? '') === 'bibliotecario') {
        header("Location: gestione_restituzioni.php");
    } else {
        header("Location: libri.php");
    }
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $errore = "Compila tutti i campi.";
    } else {

        $utente = trova_utente_da_username($pdo, $username);

        if (!$utente) {
            $errore = "Credenziali non valide.";
        } elseif (!password_verify($password, $utente['password_hash'])) {
            $errore = "Credenziali non valide.";
        } else {
            // 1) Genera OTP + salva in tabella session (scade 2 minuti)
            $codice = crea_otp($pdo, (int)$utente['id_utente']);

            // 2) Invia OTP via email (arriva su Mailpit)
            invia_email_otp((string)$utente['email'], $codice);

            // 3) Sessione provvisoria (in attesa OTP)
            $_SESSION['stato_login'] = 'OTP_IN_ATTESA';
            $_SESSION['id_utente_otp'] = (int)$utente['id_utente'];
            $_SESSION['scadenza_otp_sessione'] = time() + 120;

            header("Location: otp.php");
            exit;
        }
    }
}

require __DIR__ . "/header.php";
?>

<h1>Login BiblioTech</h1>

<?php if ($errore): ?>
  <div class="msg errore"><?= htmlspecialchars($errore) ?></div>
<?php endif; ?>

<?php if ($info): ?>
  <div class="msg"><?= htmlspecialchars($info) ?></div>
<?php endif; ?>

<form method="post" autocomplete="off">
  <label>Username</label>
  <input name="username" required>

  <label>Password</label>
  <input name="password" type="password" required>

  <button type="submit">Accedi</button>
</form>

<?php require __DIR__ . "/footer.php"; ?>
