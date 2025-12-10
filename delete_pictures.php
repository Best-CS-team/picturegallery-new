<?php

$page_title = "Delete pictures";

include 'mysqli_connect.php';
include 'includes/header.html';
include 'includes/navbar.html';

if (!isset($_SESSION['username'])) {
    header('location: index.php');
    exit();
}

if (isset($_POST['pictures_name'])) {

    $pictures_name = $_POST['pictures_name'];

    // Requête préparée pour supprimer l'entrée
    $stmt = $connection->prepare("DELETE FROM pictures WHERE pictures_name = ?");
    $stmt->bind_param("s", $pictures_name);

    if ($stmt->execute()) {

        $path = "uploads/" . $pictures_name;

        if (file_exists($path) && unlink($path)) {
            echo "Removed picture " . htmlspecialchars($path) . "<br>";
            echo "Removed picture " . htmlspecialchars($pictures_name) . ", continue with ";
            echo "<a href=''>deleting pictures</a>";
        }
    }

    $stmt->close();
}

// Requête préparée pour la sélection
$sql = "SELECT users.users_username, pictures.pictures_name 
        FROM pictures 
        INNER JOIN users ON pictures.id_users = users.users_id";

$result = $connection->query($sql);

echo "<form action='' method='POST'>";
echo "<select name='pictures_name'>";

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        if ($row['users_username'] === $_SESSION['username']) {
            echo "<option value='" . htmlspecialchars($row['pictures_name']) . "'>"
                 . htmlspecialchars($row['pictures_name']) .
                 "</option>";
        }
    }
} else {
    echo "<option>Error loading pictures</option>";
}

echo "</select>";
echo "<input type='submit' value='Delete picture'>";
echo "</form>";

include 'includes/footer.html';

$connection->close();
unset($connection);

?>
