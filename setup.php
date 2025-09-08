<?php
// setup.php - erstellt die SQLite-Datenbank für den Chatbot
$dbFile = 'data.sqlite';

// Alte Datenbank sichern oder löschen
if (file_exists($dbFile)) {
    rename($dbFile, 'data_backup_'.date('Ymd_His').'.sqlite');
    echo "Alte Datenbank gesichert.\n";
}

// Neue Datenbank erstellen
$db = new SQLite3($dbFile);

// Tabelle erstellen
$db->exec("
CREATE TABLE IF NOT EXISTS tickets (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    created_at TEXT,
    firstname TEXT,
    lastname TEXT,
    company TEXT,
    phone TEXT,
    email TEXT,
    customer_id TEXT,
    category TEXT,
    priority TEXT,
    status TEXT,
    title TEXT,
    description TEXT,
    expected TEXT,
    actual TEXT,
    error_text TEXT,
    first_occurrence TEXT,
    frequency TEXT,
    affected_users TEXT,
    os_version TEXT,
    software_version TEXT,
    device_model TEXT,
    network TEXT,
    last_changes TEXT,
    restart_result TEXT,
    self_solution TEXT
);
");

echo "✅ Datenbank 'data.sqlite' wurde erfolgreich erstellt.\n";
echo "Alle Spalten sind korrekt angelegt.\n";
echo "Du kannst jetzt den Chatbot starten und Tickets erstellen.\n";