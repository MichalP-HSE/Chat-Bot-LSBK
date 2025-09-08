<?php
session_start();
$db = new SQLite3('data.sqlite');

// Tabelle erstellen (falls noch nicht vorhanden)
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

// Fragenliste
$questions = [
"firstname_lastname" => "Wie heißt die meldende Person?",
"company" => "Welche Firma/Organisation?",
"contact" => "Telefonnummer und E-Mail für Rückfragen",
"customer_id" => "Kundennummer / Vertragsnummer (falls vorhanden)?",
"category" => "Welcher Bereich? (Software/Hardware/Netzwerk)",
"priority" => "Wie hoch ist die Priorität? (hoch/mittel/niedrig)",
"status" => "Status des Tickets? (offen/neu)",
"title" => "Kurztitel des Problems?",
"description" => "Bitte beschreibe das Problem detailliert:",
"expected" => "Welches Verhalten erwartest du?",
"actual" => "Welches Verhalten tritt tatsächlich auf?",
"error_text" => "Gibt es Fehlermeldungstexte?",
"first_occurrence" => "Wann trat das Problem erstmals auf?",
"frequency" => "Wie oft tritt es auf? (einmalig/wiederholt/permanent)",
"affected_users" => "Welche Nutzer sind betroffen?",
"os_version" => "Betriebssystem + Version?",
"software_version" => "Software/Anwendung + Version?",
"device_model" => "Gerätetyp / Modell?",
"network" => "Welche Netzwerkverbindung? (LAN/WLAN/VPN)",
"last_changes" => "Gab es letzte Änderungen?",
"restart_result" => "Wurde ein Neustart versucht? Ergebnis?",
"self_solution" => "Welche eigenen Lösungsversuche wurden unternommen?"
];

// Session initialisieren
if (!isset($_SESSION['step'])) {
    $_SESSION['step'] = 0;
    $_SESSION['answers'] = [];
}

// POST-Verarbeitung
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $keys = array_keys($questions);
    $currentKey = $keys[$_SESSION['step']];

    if ($currentKey === "firstname_lastname") {
        $_SESSION['answers']['firstname'] = $_POST['firstname'] ?? '';
        $_SESSION['answers']['lastname'] = $_POST['lastname'] ?? '';
    } elseif ($currentKey === "contact") {
        $_SESSION['answers']['phone'] = $_POST['phone'] ?? '';
        $_SESSION['answers']['email'] = $_POST['email'] ?? '';
    } elseif ($currentKey === "first_occurrence") {
        $_SESSION['answers']['first_occurrence'] = ($_POST['date'] ?? '') . ' ' . ($_POST['time'] ?? '');
    } else {
        $_SESSION['answers'][$currentKey] = $_POST['answer'] ?? '';
    }

    $_SESSION['step']++;

    // Alle Fragen beantwortet → speichern
    if ($_SESSION['step'] >= count($questions)) {
        $stmt = $db->prepare("
            INSERT INTO tickets (
                created_at, firstname, lastname, company, phone, email, customer_id, category, priority, status,
                title, description, expected, actual, error_text, first_occurrence, frequency,
                affected_users, os_version, software_version, device_model, network,
                last_changes, restart_result, self_solution
            ) VALUES (
                :created_at, :firstname, :lastname, :company, :phone, :email, :customer_id, :category, :priority, :status,
                :title, :description, :expected, :actual, :error_text, :first_occurrence, :frequency,
                :affected_users, :os_version, :software_version, :device_model, :network,
                :last_changes, :restart_result, :self_solution
            )
        ");

        $stmt->bindValue(':created_at', date("Y-m-d H:i:s"), SQLITE3_TEXT);

        // Alle Felder aus Session binden
        foreach ($_SESSION['answers'] as $key => $value) {
            $stmt->bindValue(':' . $key, $value, SQLITE3_TEXT);
        }

        $stmt->execute();

        // Bestätigungsseite anzeigen
        echo "<!DOCTYPE html><html lang='de'><head><meta charset='UTF-8'><title>Ticket erstellt</title>
        <style>
            body { font-family: Arial, sans-serif; padding: 20px; background:#f2f2f2; }
            .box { background:white; padding:20px; border-radius:10px; max-width:500px; margin:auto; text-align:center; box-shadow:0 0 10px #ccc; }
            a { display:inline-block; margin:10px; padding:10px 15px; background:#007BFF; color:white; text-decoration:none; border-radius:5px; }
            a:hover { background:#0056b3; }
        </style>
        </head><body>";
        echo "<div class='box'>";
        echo "<h2>✅ Ticket erfolgreich erstellt!</h2>";
        echo "<a href='chatbot.php'>➕ Neues Ticket erstellen</a>";
        echo "<a href='ticket_list.php'>📋 Ticket-Übersicht</a>";
        echo "</div></body></html>";

        // Session zurücksetzen
        session_destroy();
        exit;
    }
}

// Aktuelle Frage
$keys = array_keys($questions);
$currentKey = $keys[$_SESSION['step']];
$currentQuestion = $questions[$currentKey];
?>
<!DOCTYPE html>
<html lang="de">
<head>
<meta charset="UTF-8">
<title>Chatbot Ticket-System</title>
<style>
body { font-family: Arial, sans-serif; background:#f2f2f2; padding:20px; }
.chatbox{background:white; max-width:600px; margin:auto; padding:20px; border-radius:10px; box-shadow:0 0 10px #ccc;}
.bot{background:#e6f7ff;padding:10px;border-radius:5px;margin-bottom:10px;}
.user{background:#d9fdd3;padding:10px;border-radius:5px;margin-bottom:10px;text-align:right;}
input, select, textarea{width:100%; padding:10px; border-radius:5px; border:1px solid #ccc; margin-top:10px;}
input[type=submit]{background:#007BFF;color:white;cursor:pointer;}
input[type=submit]:hover{background:#0056b3;}
</style>
</head>
<body>
<div class="chatbox">
<h2>💬 Service-Request Chatbot</h2>

<?php foreach($_SESSION['answers'] as $q=>$a): ?>
<div class="bot"><?= htmlspecialchars($questions[$q] ?? ucfirst($q)) ?></div>
<div class="user"><?= htmlspecialchars($a) ?></div>
<?php endforeach; ?>

<div class="bot"><?= htmlspecialchars($currentQuestion) ?></div>
<form method="post">
<?php
if($currentKey==="firstname_lastname"): ?>
<input type="text" name="firstname" placeholder="Vorname" required>
<input type="text" name="lastname" placeholder="Nachname" required>
<?php elseif($currentKey==="contact"): ?>
<input type="text" name="phone" placeholder="Telefonnummer" required>
<input type="email" name="email" placeholder="E-Mail" required>
<?php elseif($currentKey==="first_occurrence"): ?>
<input type="date" name="date" required>
<input type="time" name="time" required>
<?php elseif($currentKey==="priority"): ?>
<select name="answer" required><option value="hoch">hoch</option><option value="mittel">mittel</option><option value="niedrig">niedrig</option></select>
<?php elseif($currentKey==="status"): ?>
<select name="answer" required><option value="offen">offen</option><option value="neu">neu</option></select>
<?php elseif($currentKey==="frequency"): ?>
<select name="answer" required><option value="einmalig">einmalig</option><option value="wiederholt">wiederholt</option><option value="permanent">permanent</option></select>
<?php elseif($currentKey==="category"): ?>
<select name="answer" required><option value="Software">Software</option><option value="Hardware">Hardware</option><option value="Netzwerk">Netzwerk</option></select>
<?php elseif($currentKey==="network"): ?>
<select name="answer" required><option value="LAN">LAN</option><option value="WLAN">WLAN</option><option value="VPN">VPN</option></select>
<?php elseif($currentKey==="affected_users"): ?>
<select name="answer" required><option value="Nur ich">Nur ich</option><option value="Mein Nutzerkreis">Mein Nutzerkreis</option><option value="Meine ganze Abteilung">Meine ganze Abteilung</option></select>
<?php elseif(in_array($currentKey, ["description","expected","actual","error_text","last_changes","restart_result","self_solution"])): ?>
<textarea name="answer" required></textarea>
<?php else: ?>
<input type="text" name="answer" required>
<?php endif; ?>

<input type="submit" value="Weiter ➡">
</form>
</div>
</body>
</html>