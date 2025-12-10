<?php
session_start();

$page_title = "Delete pictures";

include 'mysqli_connect.php';
include 'includes/header.html';
include 'includes/navbar.html';

if (!isset($_SESSION['username'])) {
    header('location: index.php');
    exit();
}

// ---- CSRF ----
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("CSRF validation failed.");
    }
}
// Génération du jeton CSRF
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
// ------------------------------


// On récupère toutes les images appartenant à l'utilisateur
$stmtList = $connection->prepare("
    SELECT pictures_name 
    FROM pictures 
    INNER JOIN users ON pictures.id_users = users.users_id
    WHERE users.users_username = ?
");
$stmtList->bind_param("s", $_SESSION['username']);
$stmtList->execute();
$result = $stmtList->get_result();

// On crée une whitelist des noms d’images valides
$validPictures = [];
while ($row = $result->fetch_assoc()) {
    $validPictures[] = $row['pictures_name'];
}
$stmtList->close();


// ----- PARTIE SUPPRESSION -----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pictures_name'])) {

    $pictures_name = $_POST['pictures_name'];

    // Vérification whitelist → empêche SSRF, LFI, path traversal
    if (!in_array($pictures_name, $validPictures, true)) {
        die("Invalid picture name.");
    }

    // Delete DB entry
    $stmt = $connection->prepare("DELETE FROM pictures WHERE pictures_name = ?");
    $stmt->bind_param("s", $pictures_name);

    if ($stmt->execute()) {

        $path = __DIR__ . "/uploads/" . basename($pictures_name); 
        // basename empêche "../" et attaque LFI

        if (is_file($path)) {
            unlink($path);
        }

        echo "<p>Picture <strong>" . htmlspecialchars($pictures_name) . "</strong> removed.</p>";
    }

    $stmt->close();
}


// ----- FORMULAIRE -----
echo "<form action='' method='POST'>";
echo "<input type='hidden' name='csrf_token' value='" . $_SESSION['csrf_token'] . "'>";
echo "<select name='pictures_name'>";

foreach ($validPictures as $pic) {
    echo "<option value='" . htmlspecialchars($pic) . "'>"
         . htmlspecialchars($pic) .
         "</option>";
}

echo "</select>";
echo "<input type='submit' value='Delete picture'>";
echo "</form>";

include 'includes/footer.html';

$connection->close();
unset($connection);

?>
