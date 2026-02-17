<?php
declare(strict_types=1);

require __DIR__ . "/config.php";
require __DIR__ . "/funzioni.php";

richiedi_login();
richiedi_ruolo('studente');

$id_utente = (int)$_SESSION['id_utente'];
$prestiti = prestiti_attivi_studente($pdo, $id_utente);
?>
<!doctype html>
<html lang="it">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>I miei prestiti — Biblioteca</title>
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
      <li><a href="catalogo.php">Catalogo</a></li>
      <li><a href="prestiti.php" class="active">I miei prestiti</a></li>
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
        <h1>I miei prestiti</h1>
        <p>Libri che hai attualmente in prestito dalla biblioteca.</p>
      </div>
      <a href="catalogo.php" class="btn btn--ghost">← Torna al catalogo</a>
    </header>

    <!-- Stato vuoto -->
    <?php if (!$prestiti): ?>

      <div class="empty-state">
        <span class="empty-state__icon">📭</span>
        <p class="empty-state__title">Nessun prestito attivo</p>
        <p class="empty-state__sub">Non hai libri in prestito al momento.</p>
        <a href="catalogo.php" class="btn btn--primary">Sfoglia il catalogo</a>
      </div>

    <?php else: ?>

      <!-- Contatore -->
      <p class="list-count">
        <?= count($prestiti) ?> prestit<?= count($prestiti) === 1 ? 'o' : 'i' ?> attiv<?= count($prestiti) === 1 ? 'o' : 'i' ?>
      </p>

      <section class="card">
        <table>
          <thead>
            <tr>
              <th>Titolo</th>
              <th>Autore</th>
              <th>Data inizio</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($prestiti as $p): ?>
              <tr>
                <td data-label="Titolo">
                  <?= htmlspecialchars($p['titolo']) ?>
                </td>
                <td data-label="Autore">
                  <?= htmlspecialchars($p['autore']) ?>
                </td>
                <td data-label="Data inizio">
                  <span class="date"><?= htmlspecialchars($p['data_inizio']) ?></span>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </section>

    <?php endif; ?>

  </main>

</body>
</html>

<h1>I miei prestiti attivi</h1>

<p>
  Ciao <b><?= htmlspecialchars($_SESSION['username']) ?></b> |
  <a href="libri.php">← Torna al catalogo</a> |
  <a href="logout.php">Logout</a>
</p>

<?php if (!$prestiti): ?>
  <p>Nessun prestito attivo.</p>
<?php else: ?>
  <table>
    <tr>
      <th>Data inizio</th>
      <th>Titolo</th>
      <th>Autore</th>
    </tr>

    <?php foreach ($prestiti as $p): ?>
      <tr>
        <td><?= htmlspecialchars($p['data_inizio']) ?></td>
        <td><?= htmlspecialchars($p['titolo']) ?></td>
        <td><?= htmlspecialchars($p['autore']) ?></td>
      </tr>
    <?php endforeach; ?>
  </table>
<?php endif; ?>

</body>
</html>
