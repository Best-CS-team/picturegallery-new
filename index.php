<?php

$page_title = "Home";

include 'includes/header.html';

include 'includes/navbar.html';

if (isset ($_SESSION ['username'])){
  echo "Logged in with name '" . htmlspecialchars($_SESSION['username'], ENT_QUOTES, 'UTF-8') . "'";
}
else{

  include 'includes/welcome_unauthenticated.html';
?>




<?php

}

include 'includes/footer.html';
?>