<?php
session_start();
if (!isset($_SESSION['loggedin'])) {
    header("Location: ticket_list.php");
    exit;
}

if (!isset($_GET['id'])) {
    die("❌ Kein Ticket ausgewählt!");
}

$db = new SQLite3('data.sqlite');
$id = intval($_GET['id']);
$stmt = $db->prepare("SELECT * FROM tickets WHERE id=:id");
$stmt->bindValue(':id', $id, SQLITE3_INTEGER);
$result = $stmt->execute();
$ticket = $result->fetchArray(SQLITE3_ASSOC);

if (!$ticket) {
    die("❌ Ticket nicht gefunden!");
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
<meta charset="UTF-8">
<title>Ticket Details</title>
<style>
body { font-family: Arial, sans-serif; background:#f2f2f2; padding:20px; }
.container { max-width:700px; margin:auto; background:white; padding:20px; border-radius:10px; box-shadow:0 0 10px #ccc; }
h2 { text-align:center; }
p { margin: 5px 0; }
label { font-weight:bold; }
a { display:inline-block; margin-top:15px; padding:10px 15px; background:#007BFF; color:white; text-decoration:none; border-radius:5px; }
a:hover { background:#0056b3; }
</style>
</head>
<body>
<div class="container">
<h2>👁️ Ticket Details (ID <?= $ticket['id'] ?>)</h2>

<p><label>Erstellt am:</label> <?= htmlspecialchars($ticket['created_at']) ?></p>
<p><label>Name:</label> <?= htmlspecialchars(($ticket['firstname'] ?? '') . ' ' . ($ticket['lastname'] ?? '')) ?></p>
<p><label>Firma:</label> <?= htmlspecialchars($ticket['company']) ?></p>
<p><label>E-Mail:</label> <?= htmlspecialchars($ticket['email']) ?></p>
<p><label>Telefon:</label> <?= htmlspecialchars($ticket['phone']) ?></p>
<p><label>Kategorie:</label> <?= htmlspecialchars($ticket['category']) ?></p>
<p><label>Priorität:</label> <?= htmlspecialchars($ticket['priority']) ?></p>
<p><label>Status:</label> <?= htmlspecialchars($ticket['status']) ?></p>
<p><label>Kurztitel:</label> <?= htmlspecialchars($ticket['title']) ?></p>
<p><label>Beschreibung:</label> <?= nl2br(htmlspecialchars($ticket['description'])) ?></p>
<p><label>Erwartetes Verhalten:</label> <?= nl2br(htmlspecialchars($ticket['expected'])) ?></p>
<p><label>Tatsächliches Verhalten:</label> <?= nl2br(htmlspecialchars($ticket['actual'])) ?></p>
<p><label>Fehlermeldungstexte:</label> <?= nl2br(htmlspecialchars($ticket['error_text'])) ?></p>
<p><label>Erstauftreten:</label> <?= htmlspecialchars($ticket['first_occurrence']) ?></p>
<p><label>Häufigkeit:</label> <?= htmlspecialchars($ticket['frequency']) ?></p>
<p><label>Betroffene Nutzer:</label> <?= htmlspecialchars($ticket['affected_users']) ?></p>
<p><label>Betriebssystem / Version:</label> <?= htmlspecialchars($ticket['os_version']) ?></p>
<p><label>Software / Version:</label> <?= htmlspecialchars($ticket['software_version']) ?></p>
<p><label>Gerätetyp / Modell:</label> <?= htmlspecialchars($ticket['device_model']) ?></p>
<p><label>Netzwerk:</label> <?= htmlspecialchars($ticket['network']) ?></p>
<p><label>Letzte Änderungen:</label> <?= nl2br(htmlspecialchars($ticket['last_changes'])) ?></p>
<p><label>Neustart durchgeführt:</label> <?= nl2br(htmlspecialchars($ticket['restart_result'])) ?></p>
<p><label>Eigenständige Lösungsversuche:</label> <?= nl2br(htmlspecialchars($ticket['self_solution'])) ?></p>

<a href="ticket_list.php">🔙 Zurück zur Übersicht</a>
</div>
</body>
</html>