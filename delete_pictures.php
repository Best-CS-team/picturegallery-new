<?php
session_start();

$page_title = "Delete pictures";

include 'mysqli_connect.php';
include 'includes/header.html';
include 'includes/navbar.html';

// Redirection si non connecté
if (!isset($_SESSION['username'])) {
    header('location: index.php');
    exit();
}

// Génération du token CSRF si absent
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// ----- PARTIE SUPPRESSION -----
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Vérification CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("CSRF validation failed.");
    }

    if (isset($_POST['pictures_name'])) {

        $pictures_name = $_POST['pictures_name'];

        // --- Récupérer les fichiers valides pour l'utilisateur ---
        $stmtList = $connection->prepare("
            SELECT pictures_name 
            FROM pictures 
            INNER JOIN users ON pictures.id_users = users.users_id
            WHERE users.users_username = ?
        ");
        $stmtList->bind_param("s", $_SESSION['username']);
        $stmtList->execute();
        $result = $stmtList->get_result();

        $validPictures = [];
        while ($row = $result->fetch_assoc()) {
            $validPictures[] = $row['pictures_name'];
        }
        $stmtList->close();

        // Vérification whitelist
        if (!in_array($pictures_name, $validPictures, true)) {
            die("Invalid picture name.");
        }

        // --- Suppression en base ---
        $stmt = $connection->prepare("DELETE FROM pictures WHERE pictures_name = ?");
        $stmt->bind_param("s", $pictures_name);

        if ($stmt->execute()) {

            // --- Suppression fichier côté serveur ---
            $uploadsDir = __DIR__ . "/uploads/";
            $safeFilename = basename($pictures_name); // empêche "../" et path traversal
            $path = $uploadsDir . $safeFilename;

            if (is_file($path) && unlink($path)) {
                echo "<p>Picture <strong>" . htmlspecialchars($safeFilename) . "</strong> removed successfully.</p>";
            } else {
                echo "<p>Picture database entry removed, but file not found on server.</p>";
            }
        } else {
            echo "<p>Error deleting picture from database.</p>";
        }

        $stmt->close();
    }
}

// ----- FORMULAIRE -----
// Récupérer toutes les images de l'utilisateur pour la liste déroulante
$stmtList = $connection->prepare("
    SELECT pictures_name 
    FROM pictures 
    INNER JOIN users ON pictures.id_users = users.users_id
    WHERE users.users_username = ?
");
$stmtList->bind_param("s", $_SESSION['username']);
$stmtList->execute();
$result = $stmtList->get_result();

$validPictures = [];
while ($row = $result->fetch_assoc()) {
    $validPictures[] = $row['pictures_name'];
}
$stmtList->close();

echo "<form action='' method='POST'>";
echo "<input type='hidden' name='csrf_token' value='" . $_SESSION['csrf_token'] . "'>";
echo "<label>Select picture to delete:</label><br>";
echo "<select name='pictures_name'>";

foreach ($validPictures as $pic) {
    echo "<option value='" . htmlspecialchars($pic) . "'>" . htmlspecialchars($pic) . "</option>";
}

echo "</select><br><br>";
echo "<input type='submit' class='btn btn-outline-danger' value='Delete picture'>";
echo "</form>";

include 'includes/footer.html';

$connection->close();
unset($connection);
?>
