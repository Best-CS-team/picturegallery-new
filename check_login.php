<?php

$page_title = 'Check login';

include 'includes/header.html';
include 'mysqli_connect.php';

// ---- CSRF PROTECTION ----
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    die("CSRF validation failed.");
}
// --------------------------

if (isset($_SESSION['username'])) {
    header('location: index.php');
    exit();
}

$username = $_POST['username'] ?? '';
$password = $_POST['pass'] ?? '';

// Requête préparée sécurisée
$stmt = $connection->prepare(
    "SELECT users_username, users_password 
     FROM users 
     WHERE users_username = ? AND users_password = ?"
);
$stmt->bind_param("ss", $username, $password);
$stmt->execute();

$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {

    while ($row = $result->fetch_assoc()) {
        $_SESSION['username'] = $row['users_username'];
        include 'includes/navbar.html';
        include 'includes/logged.php';
    }

} else {
    include 'includes/navbar.html';
    include 'includes/notlogged.php';
}

$stmt->close();
$connection->close();

include 'includes/footer.html';

?>
