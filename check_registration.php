<?php

$page_title = "Check registration";

include 'mysqli_connect.php';
include 'includes/header.html';
include 'includes/navbar.html';

if (isset($_SESSION['username'])) {
    header('location: index.php');
    exit();
}

$username = $_POST['username'] ?? '';
$password = $_POST['pass'] ?? '';

// Vérification si l'utilisateur existe (requête préparée)
$stmt1 = $connection->prepare(
    "SELECT users_username FROM users WHERE users_username = ? AND users_password = ?"
);
$stmt1->bind_param("ss", $username, $password);
$stmt1->execute();
$stmt1->store_result();

if ($stmt1->num_rows === 0) {

    // Inscription : requête préparée sécurisée
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
