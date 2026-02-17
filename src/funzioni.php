<?php
declare(strict_types=1);

/* ===================== AUTENTICAZIONE & RUOLI ===================== */

function trova_utente_da_username(PDO $pdo, string $username): ?array {
    $stmt = $pdo->prepare("SELECT id_utente, username, email, password_hash, ruolo FROM utente WHERE username = ?");
    $stmt->execute([$username]);
    $u = $stmt->fetch();
    return $u ?: null;
}

function richiedi_login(): void {
    if (empty($_SESSION['autenticato'])) {
        header("Location: login.php");
        exit;
    }
}

function richiedi_ruolo(string $ruolo): void {
    if (($_SESSION['ruolo'] ?? '') !== $ruolo) {
        http_response_code(403);
        echo "Accesso negato.";
        exit;
    }
}

/* ===================== INVIO EMAIL (SMTP diretto a Mailpit) ===================== */

function invia_email_mailpit(string $destinatario, string $oggetto, string $testo): bool {
    $sock = @fsockopen('mailpit', 1025, $errno, $errstr, 5);
    if (!$sock) return false;

    $leggi = fn() => fgets($sock, 512);
    $scrivi = fn(string $c) => fwrite($sock, $c . "\r\n");

    $leggi(); // banner
    $scrivi("EHLO bibliotech.local");
    while (($line = $leggi()) && substr($line, 3, 1) === '-') { /* continua */ }

    $scrivi("MAIL FROM:<noreply@bibliotech.local>"); $leggi();
    $scrivi("RCPT TO:<{$destinatario}>");           $leggi();
    $scrivi("DATA");                                 $leggi();

    $msg  = "From: BiblioTech <noreply@bibliotech.local>\r\n";
    $msg .= "To: {$destinatario}\r\n";
    $msg .= "Subject: {$oggetto}\r\n";
    $msg .= "MIME-Version: 1.0\r\n";
    $msg .= "Content-Type: text/plain; charset=UTF-8\r\n\r\n";
    $msg .= $testo . "\r\n";

    $scrivi($msg . ".");
    $leggi();
    $scrivi("QUIT");
    fclose($sock);
    return true;
}

/* ===================== OTP (tabella session) ===================== */

function crea_otp(PDO $pdo, int $id_utente): string {
    $codice = (string)random_int(100000, 999999);
    $hash = password_hash($codice, PASSWORD_DEFAULT);
    $scadenza = (new DateTime('+2 minutes'))->format('Y-m-d H:i:s');

    $pdo->prepare("DELETE FROM session WHERE id_utente = ?")->execute([$id_utente]);
    $pdo->prepare("INSERT INTO session (id_utente, otp_hash, scadenza) VALUES (?, ?, ?)")
        ->execute([$id_utente, $hash, $scadenza]);

    return $codice;
}

function verifica_otp(PDO $pdo, int $id_utente, string $codice): bool {
    $stmt = $pdo->prepare("SELECT otp_hash, scadenza FROM session WHERE id_utente = ? LIMIT 1");
    $stmt->execute([$id_utente]);
    $row = $stmt->fetch();

    if (!$row) return false;
    if (strtotime($row['scadenza']) < time()) return false;

    return password_verify($codice, $row['otp_hash']);
}

function elimina_otp(PDO $pdo, int $id_utente): void {
    $pdo->prepare("DELETE FROM session WHERE id_utente = ?")->execute([$id_utente]);
}

/* ===================== LIBRI ===================== */

function lista_libri(PDO $pdo): array {
    $stmt = $pdo->query("SELECT * FROM libro ORDER BY titolo");
    return $stmt->fetchAll();
}

function dettaglio_libro(PDO $pdo, int $id_libro): ?array {
    $stmt = $pdo->prepare("SELECT * FROM libro WHERE id_libro = ?");
    $stmt->execute([$id_libro]);
    $l = $stmt->fetch();
    return $l ?: null;
}

/* ===================== PRESTITI (STUDENTE) ===================== */

function prestiti_attivi_studente(PDO $pdo, int $id_utente): array {
    $stmt = $pdo->prepare("
        SELECT p.id_prestito, p.data_inizio,
               l.id_libro, l.titolo, l.autore
        FROM prestito p
        JOIN libro l ON l.id_libro = p.id_libro
        WHERE p.id_utente = ?
          AND p.data_fine IS NULL
        ORDER BY p.data_inizio DESC
    ");
    $stmt->execute([$id_utente]);
    return $stmt->fetchAll();
}

function effettua_prestito(PDO $pdo, int $id_utente, int $id_libro): bool {
    try {
        $pdo->beginTransaction();

        // (opzionale) evita doppio prestito stesso libro allo stesso studente
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM prestito WHERE id_utente=? AND id_libro=? AND data_fine IS NULL");
        $stmt->execute([$id_utente, $id_libro]);
        if ((int)$stmt->fetchColumn() > 0) {
            $pdo->rollBack();
            return false;
        }

        $stmt = $pdo->prepare("SELECT copie_disponibili FROM libro WHERE id_libro = ? FOR UPDATE");
        $stmt->execute([$id_libro]);
        $row = $stmt->fetch();

        if (!$row || (int)$row['copie_disponibili'] <= 0) {
            $pdo->rollBack();
            return false;
        }

        $pdo->prepare("UPDATE libro SET copie_disponibili = copie_disponibili - 1 WHERE id_libro = ?")
            ->execute([$id_libro]);

        $pdo->prepare("INSERT INTO prestito (id_utente, id_libro, data_inizio, data_fine) VALUES (?, ?, CURDATE(), NULL)")
            ->execute([$id_utente, $id_libro]);

        $pdo->commit();
        return true;

    } catch (Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        return false;
    }
}

/* ===================== PRESTITI (BIBLIOTECARIO) ===================== */

function prestiti_attivi_tutti(PDO $pdo): array {
    $stmt = $pdo->query("
        SELECT p.id_prestito, p.data_inizio,
               u.username AS studente,
               l.id_libro, l.titolo, l.autore
        FROM prestito p
        JOIN utente u ON u.id_utente = p.id_utente
        JOIN libro l ON l.id_libro = p.id_libro
        WHERE p.data_fine IS NULL
        ORDER BY p.data_inizio ASC
    ");
    return $stmt->fetchAll();
}

function registra_restituzione(PDO $pdo, int $id_prestito): bool {
    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("SELECT id_libro, data_fine FROM prestito WHERE id_prestito = ? FOR UPDATE");
        $stmt->execute([$id_prestito]);
        $p = $stmt->fetch();

        if (!$p || $p['data_fine'] !== null) {
            $pdo->rollBack();
            return false;
        }

        $id_libro = (int)$p['id_libro'];

        $pdo->prepare("UPDATE prestito SET data_fine = CURDATE() WHERE id_prestito = ?")
            ->execute([$id_prestito]);

        $pdo->prepare("UPDATE libro SET copie_disponibili = copie_disponibili + 1 WHERE id_libro = ?")
            ->execute([$id_libro]);

        $pdo->commit();
        return true;

    } catch (Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        return false;
    }
}
