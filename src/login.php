<?php
ini_set('display_errors', '1');
error_reporting(E_ALL);

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
  <title>Accedi — BiblioScuola</title>
  <link rel="stylesheet" href="app.css">
</head>
<body class="body--login">

  <!-- ===== SCHERMATA DI LOGIN ===== -->
  <main class="login-wrapper">

    <!-- Brand -->
    <div class="login-brand">
      <span class="login-brand__icon">📚</span>
      <h1 class="login-brand__name">BiblioScuola</h1>
      <p class="login-brand__sub">Gestione biblioteca scolastica</p>
    </div>

    <!-- Card form -->
    <section class="card login-card">

      <header class="login-card__header">
        <h2>Accedi</h2>
        <p>Inserisci le tue credenziali per continuare.</p>
      </header>

      <?php if ($errore): ?>
        <div class="msg msg--error" role="alert">
          ✕ <?= htmlspecialchars($errore) ?>
        </div>
      <?php endif; ?>

      <form method="post" class="login-form" novalidate>

        <div class="form-group">
          <label for="username">Username</label>
          <input
            type="text"
            id="username"
            name="username"
            placeholder="es. mario.rossi"
            autocomplete="username"
            required
          >
        </div>

        <div class="form-group">
          <label for="password">Password</label>
          <input
            type="password"
            id="password"
            name="password"
            placeholder="••••••••"
            autocomplete="current-password"
            required
          >
        </div>

        <button type="submit" class="btn btn--primary btn--full">
          Accedi →
        </button>

      </form>

    </section>

    <!-- Link di servizio (solo sviluppo) -->
    <?php if (defined('APP_DEBUG') && APP_DEBUG): ?>
      <p class="login-dev-note">
        📬 Debug: <a href="http://localhost:8025" target="_blank" rel="noopener">Apri Mailpit inbox</a>
      </p>
    <?php endif; ?>

  </main>

</body>
</html>