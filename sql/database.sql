-- UTENTE
CREATE TABLE utente (
    id_utente INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    ruolo ENUM('studente','bibliotecario') NOT NULL,
    data_creazione TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

ALTER TABLE utente
  ADD UNIQUE KEY uq_username (username),
  ADD UNIQUE KEY uq_email (email);

-- LIBRO
CREATE TABLE libro (
    id_libro INT AUTO_INCREMENT PRIMARY KEY,
    titolo VARCHAR(150) NOT NULL,
    autore VARCHAR(100) NOT NULL,
    isbn VARCHAR(20) UNIQUE,
    anno INT,
    copie_totali INT NOT NULL CHECK (copie_totali >= 0),
    copie_disponibili INT NOT NULL CHECK (copie_disponibili >= 0)
);


-- PRESTITO
CREATE TABLE prestito (
    id_prestito INT AUTO_INCREMENT PRIMARY KEY,
    id_utente INT NOT NULL,
    id_libro INT NOT NULL,
    data_inizio DATE NOT NULL,
    data_fine DATE NULL,
    FOREIGN KEY (id_utente) REFERENCES utente(id_utente) ON DELETE CASCADE,
    FOREIGN KEY (id_libro) REFERENCES libro(id_libro) ON DELETE CASCADE
);


-- SESSION (OTP)
CREATE TABLE session (
    id_session INT AUTO_INCREMENT PRIMARY KEY,
    id_utente INT NOT NULL,
    otp_hash VARCHAR(255) NOT NULL,
    scadenza DATETIME NOT NULL,
    FOREIGN KEY (id_utente) REFERENCES utente(id_utente) ON DELETE CASCADE
);

-- =========================
-- DATI DI TEST
-- password = "password123"

INSERT INTO utente (username, email, password_hash, ruolo) VALUES
('mario', 'mario@mail.test', '$2y$10$T7VJ4b3H4sQ0QF6YhWf6Meh6i4VZkWJj2gZ2k5p3jH0EoQmX8H7F2', 'studente'),
('luca', 'luca@mail.test', '$2y$10$T7VJ4b3H4sQ0QF6YhWf6Meh6i4VZkWJj2gZ2k5p3jH0EoQmX8H7F2', 'studente'),
('anna', 'anna@mail.test', '$2y$10$T7VJ4b3H4sQ0QF6YhWf6Meh6i4VZkWJj2gZ2k5p3jH0EoQmX8H7F2', 'studente'),
('admin', 'admin@mail.test', '$2y$10$T7VJ4b3H4sQ0QF6YhWf6Meh6i4VZkWJj2gZ2k5p3jH0EoQmX8H7F2', 'bibliotecario');

INSERT INTO libro (titolo, autore, isbn, anno, copie_totali, copie_disponibili) VALUES
('1984', 'George Orwell', '9780451524935', 1949, 5, 5),
('Il Signore degli Anelli', 'J.R.R. Tolkien', '9788845292613', 1954, 4, 4),
('Il Piccolo Principe', 'Antoine de Saint-Exupery', '9780156012195', 1943, 3, 3),
('Harry Potter e la Pietra Filosofale', 'J.K. Rowling', '9780747532699', 1997, 6, 6),
('Fahrenheit 451', 'Ray Bradbury', '9781451673319', 1953, 2, 2);
