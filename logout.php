<?php
session_start();

// IMPORTANT : aucune sortie avant les headers

// Détruire la session si active
if (isset($_SESSION['username'])) {
    session_unset();
    session_destroy();
}

// Redirection AVANT d'inclure du HTML
header("Location: index.php");
exit();
