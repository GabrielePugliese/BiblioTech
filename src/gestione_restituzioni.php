<?php
declare(strict_types=1);

require __DIR__ . "/config.php";
require __DIR__ . "/funzioni.php";

richiedi_login();
richiedi_ruolo('bibliotecario');

$prestiti = prestiti_attivi_tutti($pdo);
?>
<!doctype html>
<html lang="it">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Gestione Restituzioni — Biblioteca</title>
  <link rel="stylesheet" href="app.css">
</head>
<body data-role="bibliotecario">

  <!-- ===== NAVBAR ===== -->
  <nav class="navbar">
    <div class="navbar__brand">
      <span class="navbar__icon">📚</span>
      <span class="navbar__title">BiblioScuola</span>
    </div>

    <ul class="navbar__links">
      <li><a href="dashboard.php">Dashboard</a></li>
      <li><a href="gestione_restituzioni.php" class="active">Restituzioni</a></li>
      <li><a href="gestione_libri.php">Libri</a></li>
      <li><a href="gestione_studenti.php">Studenti</a></li>
    </ul>

    <div class="navbar__user">
      <span class="navbar__badge navbar__badge--admin">Bibliotecario</span>
      <span class="navbar__username"><?= htmlspecialchars($_SESSION['username']) ?></span>
      <a href="logout.php" class="btn btn--ghost btn--sm">Esci</a>
    </div>
  </nav>

  <!-- ===== CONTENUTO PRINCIPALE ===== -->
  <main class="container">

    <!-- Intestazione pagina -->
    <header class="page-header">
      <div>
        <h1>Gestione Restituzioni</h1>
        <p>Elenco dei prestiti attivi in attesa di restituzione.</p>
      </div>
    </header>

    <!-- Tabella prestiti -->
    <?php if (!$prestiti): ?>

      <div class="empty-state">
        <span class="empty-state__icon">✅</span>
        <p class="empty-state__title">Nessun prestito attivo</p>
        <p class="empty-state__sub">Tutti i libri sono stati restituiti.</p>
      </div>

    <?php else: ?>

      <section class="card">
        <table>
          <thead>
            <tr>
              <th>Studente</th>
              <th>Titolo</th>
              <th>Autore</th>
              <th>Data inizio</th>
              <th>Azione</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($prestiti as $p): ?>
              <tr>
                <td data-label="Studente"><?= htmlspecialchars($p['studente']) ?></td>
                <td data-label="Titolo"><?= htmlspecialchars($p['titolo']) ?></td>
                <td data-label="Autore"><?= htmlspecialchars($p['autore']) ?></td>
                <td data-label="Data inizio">
                  <span class="date"><?= htmlspecialchars($p['data_inizio']) ?></span>
                </td>
                <td data-label="Azione">
                  <form method="post" action="azione_restituzione.php">
                    <input type="hidden" name="id_prestito" value="<?= (int)$p['id_prestito'] ?>">
                    <button type="submit" class="btn btn--danger btn--sm">
                      ↩ Restituisci
                    </button>
                  </form>
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