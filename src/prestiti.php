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
  <title>I miei prestiti - BiblioTech</title>
  <link rel="stylesheet" href="app.css">
</head>

<body>

  <!-- NAVBAR (uguale alle altre pagine) -->
  <header class="navbar">
    <a class="navbar__brand" href="/libri.php">
      <span class="navbar__icon">📚</span>
      <span class="navbar__title">BiblioTech</span>
    </a>

    <ul class="navbar__links">
      <li><a href="/libri.php">Catalogo</a></li>
      <li><a class="active" href="/prestiti.php">I miei prestiti</a></li>
    </ul>

    <div class="navbar__user">
      <span class="navbar__username"><?= htmlspecialchars($_SESSION['username']) ?></span>
      <span class="navbar__badge navbar__badge--student">studente</span>
      <a class="btn btn--ghost btn--sm" href="/logout.php">Esci</a>
    </div>
  </header>

  <main>

    <div class="page-header">
      <div>
        <div class="breadcrumb">
          <a href="/libri.php">Catalogo</a> / <a href="/prestiti.php">Prestiti</a>
        </div>
        <h1>I miei prestiti attivi</h1>
        <p>Qui trovi i libri che hai attualmente in carico.</p>
      </div>

      <div style="display:flex; gap:10px; align-items:center;">
        <a class="btn btn--ghost" href="/libri.php">← Torna al catalogo</a>
      </div>
    </div>

    <section class="card">
      <?php if (!$prestiti): ?>
        <div class="empty-state">
          <div class="empty-state__icon">📭</div>
          <p class="empty-state__title">Nessun prestito attivo</p>
          <p class="empty-state__sub">Vai al catalogo per prendere un libro in prestito.</p>
          <a class="btn btn--primary" href="/libri.php">Apri catalogo</a>
        </div>
      <?php else: ?>
        <p class="list-count">Totale prestiti attivi: <?= count($prestiti) ?></p>

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
                <td><?= htmlspecialchars($p['titolo']) ?></td>
                <td><?= htmlspecialchars($p['autore']) ?></td>
                <td class="date"><?= htmlspecialchars($p['data_inizio']) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </section>

  </main>
</body>
</html>
