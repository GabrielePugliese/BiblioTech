<?php
declare(strict_types=1);

require __DIR__ . "/config.php";
require __DIR__ . "/funzioni.php";

richiedi_login();
richiedi_ruolo('studente');

$id = (int)($_GET['id'] ?? 0);
$libro = dettaglio_libro($pdo, $id);

if (!$libro) {
    http_response_code(404);
    echo "Libro non trovato.";
    exit;
}

$disp = (int)$libro['copie_disponibili'];
?>
<!doctype html>
<html lang="it">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($libro['titolo']) ?> — BiblioTech</title>
  <link rel="stylesheet" href="app.css">
</head>
<body data-role="studente">

  <!-- ===== NAVBAR ===== -->
  <nav class="navbar">
    <a class="navbar__brand" href="/libri.php">
      <span class="navbar__icon">📚</span>
      <span class="navbar__title">BiblioTech</span>
    </a>

    <ul class="navbar__links">
      <li><a href="/libri.php">Catalogo</a></li>
      <li><a href="/prestiti.php">I miei prestiti</a></li>
    </ul>

    <div class="navbar__user">
      <span class="navbar__badge navbar__badge--student">Studente</span>
      <span class="navbar__username"><?= htmlspecialchars($_SESSION['username']) ?></span>
      <a href="/logout.php" class="btn btn--ghost btn--sm">Esci</a>
    </div>
  </nav>

  <!-- ===== CONTENUTO PRINCIPALE ===== -->
  <main>

    <!-- Breadcrumb -->
    <nav class="breadcrumb" aria-label="Navigazione">
      <a href="/libri.php">← Torna al catalogo</a>
    </nav>

    <!-- Scheda libro -->
    <section class="card book-detail">

      <!-- Intestazione libro -->
      <header class="book-detail__header">
        <div class="book-detail__cover" aria-hidden="true">📖</div>
        <div class="book-detail__meta">
          <h1><?= htmlspecialchars($libro['titolo']) ?></h1>
          <p class="book-detail__author"><?= htmlspecialchars($libro['autore']) ?></p>

          <?php if ($disp > 0): ?>
            <span class="badge badge--ok">✓ Disponibile</span>
          <?php else: ?>
            <span class="badge badge--err">✕ Non disponibile</span>
          <?php endif; ?>
        </div>
      </header>

      <hr>

      <!-- Dettagli tecnici -->
      <dl class="book-detail__info">
        <div class="book-detail__info-row">
          <dt>Autore</dt>
          <dd><?= htmlspecialchars($libro['autore']) ?></dd>
        </div>

        <div class="book-detail__info-row">
          <dt>ISBN</dt>
          <dd><?= htmlspecialchars($libro['isbn'] ?? '—') ?></dd>
        </div>

        <div class="book-detail__info-row">
          <dt>Anno</dt>
          <dd><?= htmlspecialchars((string)($libro['anno'] ?? '—')) ?></dd>
        </div>

        <div class="book-detail__info-row">
          <dt>Copie totali</dt>
          <dd><?= (int)$libro['copie_totali'] ?></dd>
        </div>

        <div class="book-detail__info-row">
          <dt>Copie disponibili</dt>
          <dd>
            <span class="<?= $disp > 0 ? 'text--ok' : 'text--err' ?>">
              <?= $disp ?> / <?= (int)$libro['copie_totali'] ?>
            </span>
          </dd>
        </div>
      </dl>

      <hr>

      <!-- Azione prestito -->
      <footer class="book-detail__actions">
        <?php if ($disp > 0): ?>
          <form method="post" action="/azione_prestito.php">
            <input type="hidden" name="id_libro" value="<?= (int)$libro['id_libro'] ?>">
            <button type="submit" class="btn btn--primary">＋ Prendi in prestito</button>
          </form>
        <?php else: ?>
          <button class="btn" disabled>Non disponibile</button>
          <p class="msg msg--error">Tutte le copie sono attualmente in prestito. Riprova più tardi.</p>
        <?php endif; ?>
      </footer>

    </section>

  </main>

</body>
</html>
