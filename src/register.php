<?php
declare(strict_types=1);

require __DIR__ . "/config.php";
require __DIR__ . "/funzioni.php";

$errore = "";
$ok = "";

// se già loggato, vai alla home
if (!empty($_SESSION['autenticato'])) {
    header("Location: index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $password2 = $_POST['password2'] ?? '';

    // controlli base
    if ($username === '' || $email === '' || $password === '' || $password2 === '') {
        $errore = "Compila tutti i campi.";
    } elseif (strlen($username) < 3 || strlen($username) > 18) {
        $errore = "Username deve essere tra 3 e 18 caratteri.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errore = "Email non valida.";
    } elseif (strlen($password) < 6) {
        $errore = "Password troppo corta (min 6).";
    } elseif ($password !== $password2) {
        $errore = "Le password non coincidono.";
    } else {
        try {
            // RUOLO FISSO: solo studente
            $hash = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare("
                INSERT INTO utente (username, email, password_hash, ruolo)
                VALUES (?, ?, ?, 'studente')
            ");
            $stmt->execute([$username, $email, $hash]);

            $ok = "Registrazione completata! Ora puoi fare login.";
        } catch (PDOException $e) {
            // 1062 = duplicate key (username/email unici)
            if ((int)($e->errorInfo[1] ?? 0) === 1062) {
                $errore = "Username o email già usati.";
            } else {
                $errore = "Errore durante la registrazione.";
            }
        }
    }
}
?>
<!doctype html>
<html lang="it">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Registrazione - BiblioTech</title>
  <link rel="stylesheet" href="app.css">
</head>
<body class="body--login">

  <div class="login-wrapper">
    <div class="login-brand">
      <span class="login-brand__icon">📚</span>
      <h1 class="login-brand__name">BiblioTech</h1>
      <p class="login-brand__sub">Crea un account studente</p>
    </div>

    <section class="card login-card">
      <div class="login-card__header">
        <h2>Registrati</h2>
        <p>Inserisci i dati e poi accedi con OTP.</p>
      </div>

      <?php if ($errore): ?>
        <p style="color:red;"><?= htmlspecialchars($errore) ?></p>
      <?php endif; ?>

      <?php if ($ok): ?>
        <p style="color:green;"><?= htmlspecialchars($ok) ?></p>
      <?php endif; ?>

      <form method="post" class="login-form">
        <div class="form-group">
          <label>Username</label>
          <input name="username" maxlength="18" required value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
          <div class="form-hint">3–18 caratteri</div>
        </div>

        <div class="form-group">
          <label>Email</label>
          <input name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
        </div>

        <div class="form-group">
          <label>Password</label>
          <input type="password" name="password" required>
          <div class="form-hint">Minimo 6 caratteri</div>
        </div>

        <div class="form-group">
          <label>Ripeti password</label>
          <input type="password" name="password2" required>
        </div>

        <button class="btn btn--primary btn--full" type="submit">Crea account</button>
      </form>

      <hr>
      <p style="margin:0;">
        Hai già un account? <a href="/login.php">Vai al login</a>
      </p>
    </section>
  </div>

</body>
</html>
