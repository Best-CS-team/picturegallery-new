<?php

$page_title = 'Login';

include 'includes/header.html';

include 'includes/navbar.html';

if (isset ($_SESSION['username'])){

    echo "You are logged as <strong>"
        . htmlspecialchars($_SESSION['username'], ENT_QUOTES, 'UTF-8')
        . "</strong>. You can <a href='logout.php'>logout</a>";
}
else{
    include 'includes/login.html';
?>


<?php
}

include 'includes/footer.html';



?>

