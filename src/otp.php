<?php
ini_set('display_errors', '1');
error_reporting(E_ALL);

require __DIR__ . "/config.php";
require __DIR__ . "/funzioni.php";

if (($_SESSION['stato_login'] ?? '') !== 'OTP_IN_ATTESA') {
    header("Location: login.php");
    exit;
}

$id_utente = (int)($_SESSION['id_utente_otp'] ?? 0);
$errore = "";
$msg = "";

if (isset($_POST['rinvia'])) {
    $stmt = $pdo->prepare("SELECT email FROM utente WHERE id_utente = ?");
    $stmt->execute([$id_utente]);
    $email = (string)$stmt->fetchColumn();

    $codice = crea_otp($pdo, $id_utente);
    $testo = "Il tuo nuovo codice OTP è: $codice\nScade tra 2 minuti.";
    invia_email_mailpit($email, "Nuovo OTP BiblioTech", $testo);

    $msg = "Nuovo codice inviato. Controlla Mailpit.";
}

if (isset($_POST['verifica'])) {
    $codice = trim($_POST['codice'] ?? '');

    if (verifica_otp($pdo, $id_utente, $codice)) {
        $stmt = $pdo->prepare("SELECT id_utente, username, ruolo FROM utente WHERE id_utente = ?");
        $stmt->execute([$id_utente]);
        $u = $stmt->fetch();

        elimina_otp($pdo, $id_utente);

        $_SESSION['autenticato'] = true;
        $_SESSION['id_utente'] = (int)$u['id_utente'];
        $_SESSION['username'] = (string)$u['username'];
        $_SESSION['ruolo'] = (string)$u['ruolo'];

        unset($_SESSION['stato_login'], $_SESSION['id_utente_otp']);

        header("Location: index.php");
        exit;
    } else {
        $errore = "Codice non valido o scaduto.";
    }
}
?>
<!doctype html>
<html lang="it">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Verifica OTP — BiblioTech</title>
  <link rel="stylesheet" href="app.css">
</head>
<body class="body--login">

  <!-- ===== SCHERMATA OTP ===== -->
  <main class="login-wrapper">

    <!-- Brand -->
    <div class="login-brand">
      <span class="login-brand__icon">📚</span>
      <h1 class="login-brand__name">BiblioTech</h1>
      <p class="login-brand__sub">Gestione biblioteca scolastica</p>
    </div>

    <!-- Card OTP -->
    <section class="card login-card">

      <header class="login-card__header">
        <div class="otp-icon" aria-hidden="true">✉️</div>
        <h2>Verifica il tuo accesso</h2>
        <p>Abbiamo inviato un codice a 6 cifre alla tua email. Inseriscilo qui sotto per continuare.</p>
      </header>

      <!-- Messaggi di stato -->
      <?php if ($msg): ?>
        <div class="msg msg--success" role="status">
          ✓ <?= htmlspecialchars($msg) ?>
        </div>
      <?php endif; ?>

      <?php if ($errore): ?>
        <div class="msg msg--error" role="alert">
          ✕ <?= htmlspecialchars($errore) ?>
        </div>
      <?php endif; ?>

      <!-- Form verifica codice -->
      <form method="post" class="login-form" novalidate>
        <div class="form-group">
          <label for="codice">Codice OTP</label>
          <input
            type="text"
            id="codice"
            name="codice"
            class="input--otp"
            placeholder="000000"
            maxlength="6"
            inputmode="numeric"
            pattern="[0-9]{6}"
            autocomplete="one-time-code"
            required
          >
          <span class="form-hint">Il codice è valido per pochi minuti.</span>
        </div>

        <button type="submit" name="verifica" class="btn btn--primary btn--full">
          Verifica →
        </button>
      </form>

      <hr>

      <!-- Form rinvia codice -->
      <form method="post" class="otp-resend">
        <span class="otp-resend__label">Non hai ricevuto il codice?</span>
        <button type="submit" name="rinvia" class="btn btn--ghost btn--sm">
          ↺ Rinvia codice
        </button>
      </form>

    </section>

    <!-- Link Mailpit solo in debug -->
    <?php if (defined('APP_DEBUG') && APP_DEBUG): ?>
      <p class="login-dev-note">
        📬 Debug: <a href="http://localhost:8025" target="_blank" rel="noopener">Apri Mailpit inbox</a>
      </p>
    <?php endif; ?>

  </main>

</body>
</html>
