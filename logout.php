
<?php
session_start();

// Détruire la session
session_unset();
session_destroy();

// Rediriger vers la page d'accueil
header('Location: index.php');
exit;
?>
