<?php
session_start();

// Logout abfragen
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: ticket_list.php");
    exit;
}

// Login-Daten
$validUser = "Admin";
$validPass = "123456";

// Login prüfen
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        if ($_POST['username'] === $validUser && $_POST['password'] === $validPass) {
            $_SESSION['loggedin'] = true;
            header("Location: ticket_list.php");
            exit;
        } else {
            $error = "❌ Falscher Benutzername oder Passwort!";
        }
    }
    ?>
    <!DOCTYPE html>
    <html lang="de">
    <head>
        <meta charset="UTF-8">
        <title>Login</title>
        <style>
            body { font-family: Arial, sans-serif; background: #f2f2f2; padding: 20px; }
            form { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 0 10px #ccc; max-width: 400px; margin: auto; }
            label { font-weight: bold; display: block; margin-top: 15px; }
            input { width: 100%; padding: 8px; margin-top: 5px; border: 1px solid #ccc; border-radius: 5px; }
            button { margin-top: 20px; padding: 10px; width: 100%; border: none; border-radius: 5px; background: #007BFF; color: white; cursor: pointer; }
            button:hover { background: #0056b3; }
            .error { color: red; }
        </style>
    </head>
    <body>
        <form method="post">
            <h2>🔐 Login</h2>
            <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
            <label>Benutzername:</label>
            <input type="text" name="username" required>
            <label>Passwort:</label>
            <input type="password" name="password" required>
            <button type="submit">Einloggen</button>
        </form>
    </body>
    </html>
    <?php
    exit;
}

// Tickets abrufen
$db = new SQLite3('data.sqlite');
$result = $db->query("SELECT * FROM tickets ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="de">
<head>
<meta charset="UTF-8">
<title>Ticket-Übersicht</title>
<style>
body { font-family: Arial, sans-serif; background: #f9f9f9; padding: 20px; }
table { border-collapse: collapse; width: 100%; margin-top: 20px; background: white; }
th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
th { background: #007BFF; color: white; }
tr:nth-child(even) { background: #f2f2f2; }
.btn { display: inline-block; padding: 6px 12px; margin: 2px; background: #007BFF; color: white; text-decoration: none; border-radius: 5px; }
.btn:hover { background: #0056b3; }
.header-buttons { margin-bottom: 15px; }
</style>
</head>
<body>
<h1>📋 Ticket-Übersicht</h1>
<div class="header-buttons">
    <a class="btn" href="chatbot.php">➕ Neues Ticket erstellen</a>
    <a class="btn" href="ticket_list.php?logout=1">🚪 Logout</a>
</div>
<table>
<tr>
<th>ID</th>
<th>Erstellt am</th>
<th>Name</th>
<th>Firma</th>
<th>E-Mail</th>
<th>Telefon</th>
<th>Kategorie</th>
<th>Priorität</th>
<th>Status</th>
<th>Kurztitel</th>
<th>Aktion</th>
</tr>
<?php while ($row = $result->fetchArray(SQLITE3_ASSOC)): ?>
<tr>
<td><?= htmlspecialchars($row['id']) ?></td>
<td><?= htmlspecialchars($row['created_at']) ?></td>
<td><?= htmlspecialchars(($row['firstname'] ?? '') . ' ' . ($row['lastname'] ?? '')) ?></td>
<td><?= htmlspecialchars($row['company']) ?></td>
<td><?= htmlspecialchars($row['email']) ?></td>
<td><?= htmlspecialchars($row['phone']) ?></td>
<td><?= htmlspecialchars($row['category']) ?></td>
<td><?= htmlspecialchars($row['priority']) ?></td>
<td><?= htmlspecialchars($row['status']) ?></td>
<td><?= htmlspecialchars($row['title']) ?></td>
<td><a class="btn" href="ticket_view.php?id=<?= $row['id'] ?>">👁️ Anzeigen</a></td>
</tr>
<?php endwhile; ?>
</table>
</body>
</html>