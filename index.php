<?php
// ===== Chatbot für Service-Tickets mit Name-Erkennung =====
$dbFile = __DIR__ . '/data.sqlite';
$pdo = new PDO('sqlite:' . $dbFile);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$pdo->exec('CREATE TABLE IF NOT EXISTS service_requests (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    created_at TEXT NOT NULL,
    name TEXT,
    email TEXT,
    description TEXT,
    priority TEXT,
    status TEXT DEFAULT "neu"
)');

session_start();

if (isset($_POST['message'])) {
    $msg = trim($_POST['message']);
    
    if (!isset($_SESSION['step'])) {
        $_SESSION['step'] = 0;
        $_SESSION['ticket'] = [];
    }

    $reply = "";

    switch ($_SESSION['step']) {
        case 0: // Start
            $reply = "Hallo, ich bin dein Problemlöser. Wie kann ich dir helfen?";
            $_SESSION['step']++;
            break;
        case 1: // Name abfragen
            // Name extrahieren
            if (preg_match('/ich heiße (.+)/i', $msg, $matches) ||
                preg_match('/mein name ist (.+)/i', $msg, $matches)) {
                $name = trim($matches[1]);
            } else {
                $name = $msg; // fallback: ganze Eingabe
            }
            $_SESSION['ticket']['name'] = $name;
            $reply = "Danke, " . htmlspecialchars($name) . "! Wie lautet deine E-Mail?";
            $_SESSION['step']++;
            break;
        case 2: // E-Mail
            $_SESSION['ticket']['email'] = $msg;
            $reply = "Super! Beschreibe bitte dein Problem.";
            $_SESSION['step']++;
            break;
        case 3: // Problem
            $_SESSION['ticket']['description'] = $msg;
            $reply = "Wie wichtig ist dein Problem? (Niedrig, Mittel, Hoch)";
            $_SESSION['step']++;
            break;
        case 4: // Priorität & Ticket speichern
            $_SESSION['ticket']['priority'] = $msg;
            $stmt = $pdo->prepare("INSERT INTO service_requests (created_at, name, email, description, priority) VALUES (datetime('now'),?,?,?,?,?)");
            $stmt->execute([
                $_SESSION['ticket']['name'],
                $_SESSION['ticket']['email'],
                $_SESSION['ticket']['description'],
                $_SESSION['ticket']['priority']
            ]);

            $reply = "Danke! Dein Ticket wurde erstellt. 📝";
            $_SESSION['step'] = 0;
            $_SESSION['ticket'] = [];
            break;
    }

    echo json_encode(['reply' => $reply]);
    exit;
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
<meta charset="UTF-8">
<title>Service-Chatbot</title>
<style>
body { font-family: Arial; background: #121212; color: #e0e0e0; display: flex; flex-direction: column; align-items: center; padding: 2rem;}
h1 { color: white; }
#chat { width: 100%; max-width: 500px; height: 350px; background: #1e1e1e; border-radius: 10px; overflow-y: auto; padding: 1rem; margin-bottom: 1rem; display: flex; flex-direction: column; gap: 0.5rem;}
.message { padding: 0.5rem 1rem; border-radius: 10px; max-width: 80%; }
.user { background: #00bcd4; color: #000; align-self: flex-end; }
.bot  { background: #333; color: #fff; align-self: flex-start; }
form { display: flex; width: 100%; max-width: 500px; }
input { flex: 1; padding: 0.5rem; border-radius: 10px; border: none; margin-right: 0.5rem; background: #2a2a2a; color: white; }
button { padding: 0.5rem 1rem; border-radius: 10px; border: none; background: #00bcd4; font-weight: bold; cursor: pointer; }
button:hover { background: #0097a7; }
</style>
</head>
<body>
<h1>Service-Chatbot</h1>
<div id="chat"></div>
<form id="chatForm">
    <input type="text" id="message" placeholder="Schreibe hier..." required>
    <button>Senden</button>
</form>

<script>
const chat = document.getElementById('chat');
const form = document.getElementById('chatForm');
const input = document.getElementById('message');

function addMessage(text, cls) {
    const div = document.createElement('div');
    div.textContent = text;
    div.className = 'message ' + cls;
    chat.appendChild(div);
    chat.scrollTop = chat.scrollHeight;
}

// Startnachricht
addMessage("Hallo, ich bin dein Problemlöser. Wie kann ich dir helfen?", "bot");

form.addEventListener('submit', async e => {
    e.preventDefault();
    const msg = input.value.trim();
    if(!msg) return;

    addMessage(msg, 'user');
    input.value = '';

    const resp = await fetch('', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'message=' + encodeURIComponent(msg)
    });
    const data = await resp.json();
    addMessage(data.reply, 'bot');
});
</script>
</body>
</html>