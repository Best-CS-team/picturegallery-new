<?php

$page_title = "Check registration";

include 'mysqli_connect.php';
include 'includes/header.html';
include 'includes/navbar.html';

if (isset($_SESSION['username'])) {
    header('location: index.php');
    exit();
}

// --- CSRF CHECK ---
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    die("CSRF validation failed.");
}
// -------------------

// 🛡️ Filtrage & validation
$username = trim($_POST['username'] ?? '');
$password = trim($_POST['pass'] ?? '');

// Anti-XSS dans les données stockées
$username = htmlspecialchars($username, ENT_QUOTES, 'UTF-8');

// Vérification vide
if ($username === '' || $password === '') {
    die("Invalid form submission.");
}

// Vérification si l'utilisateur existe
$stmt1 = $connection->prepare(
    "SELECT users_username FROM users WHERE users_username = ?"
);
$stmt1->bind_param("s", $username);
$stmt1->execute();
$stmt1->store_result();

if ($stmt1->num_rows === 0) {

    // Insertion utilisateur (SANS hash, comme demandé)
    $stmt2 = $connection->prepare(
        "INSERT INTO users (users_username, users_password) VALUES (?, ?)"
    );
    $stmt2->bind_param("ss", $username, $password);

    if ($stmt2->execute()) {
        include 'includes/new_registration.php';
    } else {
        include 'includes/error.php';
    }

    $stmt2->close();

} else {
    include 'includes/notregistered.php';
}

$stmt1->close();
$connection->close();

include 'includes/footer.html';

?>
