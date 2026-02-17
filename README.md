# BiblioTech

BiblioTech è una web-app sviluppata in PHP che sostituisce il registro cartaceo dei prestiti librari scolastici con un sistema digitale centralizzato.

Gli studenti possono consultare il catalogo e prendere libri in prestito, mentre il bibliotecario gestisce le restituzioni.  
L’accesso è protetto da autenticazione con password e verifica OTP tramite email simulata.

La documentazione teorica completa (analisi, E-R, UML) si trova nella cartella **docs**.  
Questo file spiega solo come avviare il progetto.

----------------------------------------------

## Tecnologie utilizzate

Il progetto funziona interamente tramite container Docker:

- PHP 8 + Apache → logica backend
- MySQL → database
- HTML / CSS → interfaccia
- phpMyAdmin → gestione database
- MailPit → ricezione codice OTP
- Prepared Statements → protezione SQL Injection
- Password Hashing → sicurezza credenziali
- Sessioni PHP → gestione autenticazione

Non è necessario installare PHP o MySQL sul computer.


----------------------------------------------

## Requisiti

Serve solo Docker Desktop installato e avviato.

Download:
https://docs.docker.com/desktop/


----------------------------------------------

## Avvio del progetto

Aprire un terminale nella cartella del progetto ed eseguire:

    docker compose up -d --build

Il primo avvio può richiedere circa 1 minuto.

----------------------------------------------

## Accesso ai servizi

Sito web:
[http://localhost:8080]


phpMyAdmin:
[http://localhost:8081]


MailPit (per leggere OTP):
[http://localhost:8025]


----------------------------------------------

## Importazione del database

1. Aprire phpMyAdmin
2. Login:
   utente: root  
   password: root
3. Creare il database:

    bibliotech

----------------------------------------------

4. Selezionare il database → Importa
5. Caricare il file:


sql/database.sql

Il sistema è ora pronto.

----------------------------------------------

## Account di test

Studente:
username: studente1  
password: password123

Bibliotecario:
username: bibliotecario  
password: admin123

----------------------------------------------

## Registrazione nuovi utenti

Dal login è disponibile il pulsante “Registrati”.

I nuovi utenti vengono salvati nel database come studenti e possono accedere normalmente al sistema.

----------------------------------------------

## Accesso con OTP

Dopo il login con username e password:

1. Aprire MailPit
2. Copiare il codice ricevuto
3. Inserirlo entro 2 minuti

Solo dopo la verifica si accede al sistema.

----------------------------------------------

## Funzionamento del sistema

Studente:
- visualizza catalogo
- prende libri in prestito
- visualizza i propri prestiti

Bibliotecario:
- visualizza tutti i prestiti attivi
- registra le restituzioni
- aggiorna disponibilità copie automaticamente

----------------------------------------------

## Struttura del progetto

src/        codice PHP
sql/        database
docs/       documentazione
docker-compose.yml
README.md

----------------------------------------------

## Arresto del server


    docker compose down

Per cancellare anche il database:

    docker compose down -v

----------------------------------------------

## Sicurezza

- password salvate con hashing
- prepared statements contro SQL injection
- accesso tramite sessioni
- verifica OTP via email
- separazione permessi studente / bibliotecario

BiblioTech digitalizza la gestione dei prestiti mantenendo aggiornate in tempo reale le copie disponibili.
