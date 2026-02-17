<?php
declare(strict_types=1);

require __DIR__ . "/config.php";
require __DIR__ . "/funzioni.php";

$errore = "";

if (!empty($_SESSION['autenticato'])) {
    header("Location: index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $utente = trova_utente_da_username($pdo, $username);

    if (!$utente || !password_verify($password, $utente['password_hash'])) {
        $errore = "Credenziali non valide.";
    } else {
        $codice = crea_otp($pdo, (int)$utente['id_utente']);

        $testo = "Il tuo codice OTP è: $codice\nScade tra 2 minuti.";
        $ok = invia_email_mailpit((string)$utente['email'], "Codice OTP BiblioTech", $testo);

        if (!$ok) {
            $errore = "Mailpit non raggiungibile. Controlla che sia attivo.";
        } else {
            $_SESSION['stato_login'] = 'OTP_IN_ATTESA';
            $_SESSION['id_utente_otp'] = (int)$utente['id_utente'];
            header("Location: otp.php");
            exit;
        }
    }
}
?>
<!doctype html>
<html lang="it">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login - BiblioTech</title>
  <link rel="stylesheet" href="app.css">
</head>
<body class="body--login">

  <div class="login-wrapper">
    <div class="login-brand">
      <span class="login-brand__icon">📚</span>
      <h1 class="login-brand__name">BiblioTech</h1>
      <p class="login-brand__sub">Accesso con OTP</p>
    </div>

    <section class="card login-card">
      <div class="login-card__header">
        <h2>Login</h2>
        <p>Inserisci le credenziali, poi conferma con OTP.</p>
      </div>

      <?php if ($errore): ?>
        <p style="color:red;"><?= htmlspecialchars($errore) ?></p>
      <?php endif; ?>

      <form method="post" class="login-form">
        <div class="form-group">
          <label>Username</label>
          <input name="username" required maxlength="18" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
        </div>

        <div class="form-group">
          <label>Password</label>
          <input type="password" name="password" required>
        </div>

        <button class="btn btn--primary btn--full" type="submit">Accedi</button>
      </form>

      <hr>
      <p style="margin:0;">
        Non hai un account? <a href="/register.php">Registrati</a>
      </p>
    </section>

    <p class="login-dev-note">
      Mailpit: <a href="http://localhost:8025" target="_blank">apri inbox</a>
    </p>
  </div>

</body>
</html>
