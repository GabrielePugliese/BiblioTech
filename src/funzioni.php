<?php
declare(strict_types=1);

/* ================= SICUREZZA BASE ================= */

function richiedi_login(): void {
    if (empty($_SESSION['autenticato'])) {
        header("Location: login.php");
        exit;
    }
}

function richiedi_ruolo(string $ruolo): void {
    if (($_SESSION['ruolo'] ?? '') !== $ruolo) {
        http_response_code(403);
        echo "Accesso negato";
        exit;
    }
}

function rigenera_sessione(): void {
    session_regenerate_id(true);
}

function csrf_crea_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/* ================= AUTENTICAZIONE ================= */

function trova_utente_da_username(PDO $pdo, string $username): ?array {
    $stmt = $pdo->prepare("
        SELECT id_utente, username, email, password_hash, ruolo
        FROM utente
        WHERE username = ?
    ");
    $stmt->execute([$username]);
    $u = $stmt->fetch();
    return $u ?: null;
}

/* ================= OTP ================= */

function crea_otp(PDO $pdo, int $id_utente): string {

    $codice = (string)random_int(100000, 999999);
    $hash = password_hash($codice, PASSWORD_DEFAULT);

    $scadenza = (new DateTime('+2 minutes'))->format('Y-m-d H:i:s');

    $pdo->prepare("DELETE FROM session WHERE id_utente = ?")
        ->execute([$id_utente]);

    $pdo->prepare("
        INSERT INTO session (id_utente, otp_hash, scadenza)
        VALUES (?, ?, ?)
    ")->execute([$id_utente, $hash, $scadenza]);

    return $codice;
}

function verifica_otp(PDO $pdo, int $id_utente, string $codice): bool {

    $stmt = $pdo->prepare("SELECT otp_hash, scadenza FROM session WHERE id_utente = ?");
    $stmt->execute([$id_utente]);
    $row = $stmt->fetch();

    if (!$row) return false;

    if (strtotime($row['scadenza']) < time()) return false;

    return password_verify($codice, $row['otp_hash']);
}

function elimina_otp(PDO $pdo, int $id_utente): void {
    $pdo->prepare("DELETE FROM session WHERE id_utente = ?")
        ->execute([$id_utente]);
}

function invia_email_otp(string $email, string $codice): void {
    $oggetto = "Codice OTP BiblioTech";
    $messaggio = "Il tuo codice OTP è: $codice\nScade tra 2 minuti.";
    @mail($email, $oggetto, $messaggio);
}
