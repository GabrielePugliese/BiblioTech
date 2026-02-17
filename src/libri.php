<?php
require __DIR__ . "/config.php";
require __DIR__ . "/funzioni.php";

richiedi_login();
richiedi_ruolo('studente');

$libri = lista_libri($pdo);
?>
<!doctype html>
<html lang="it">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Catalogo Libri — Biblioteca</title>
  <link rel="stylesheet" href="app.css">
</head>
<body data-role="studente">

  <!-- ===== NAVBAR ===== -->
  <nav class="navbar">
    <div class="navbar__brand">
      <span class="navbar__icon">📚</span>
      <span class="navbar__title">BiblioScuola</span>
    </div>

    <ul class="navbar__links">
      <li><a href="catalogo.php" class="active">Catalogo</a></li>
      <li><a href="prestiti.php">I miei prestiti</a></li>
    </ul>

    <div class="navbar__user">
      <span class="navbar__badge navbar__badge--student">Studente</span>
      <span class="navbar__username"><?= htmlspecialchars($_SESSION['username']) ?></span>
      <a href="logout.php" class="btn btn--ghost btn--sm">Esci</a>
    </div>
  </nav>

  <!-- ===== CONTENUTO PRINCIPALE ===== -->
  <main class="container">

    <!-- Intestazione pagina -->
    <header class="page-header">
      <div>
        <h1>Catalogo Libri</h1>
        <p>Sfoglia i libri disponibili. Clicca su un titolo per i dettagli o prendi subito in prestito.</p>
      </div>
    </header>

    <!-- Tabella catalogo -->
    <section class="card">
      <table>
        <thead>
          <tr>
            <th>Titolo</th>
            <th>Autore</th>
            <th>Disponibilità</th>
            <th>Azione</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($libri as $l): ?>
            <?php $disp = (int)$l['copie_disponibili']; ?>
            <tr>
              <td data-label="Titolo">
                <a href="libro.php?id=<?= (int)$l['id_libro'] ?>">
                  <?= htmlspecialchars($l['titolo']) ?>
                </a>
              </td>

              <td data-label="Autore">
                <?= htmlspecialchars($l['autore']) ?>
              </td>

              <td data-label="Disponibilità">
                <?php if ($disp > 2): ?>
                  <span class="badge badge--ok">✓ <?= $disp ?> disponibili</span>
                <?php elseif ($disp > 0): ?>
                  <span class="badge badge--warn">⚠ <?= $disp ?> rimast<?= $disp === 1 ? 'a' : 'e' ?></span>
                <?php else: ?>
                  <span class="badge badge--err">✕ Non disponibile</span>
                <?php endif; ?>
              </td>

              <td data-label="Azione">
                <?php if ($disp > 0): ?>
                  <form method="post" action="azione_prestito.php">
                    <input type="hidden" name="id_libro" value="<?= (int)$l['id_libro'] ?>">
                    <button type="submit" class="btn btn--primary btn--sm">
                      ＋ Prendi in prestito
                    </button>
                  </form>
                <?php else: ?>
                  <button class="btn btn--sm" disabled>Non disponibile</button>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </section>

  </main>

</body>
</html>