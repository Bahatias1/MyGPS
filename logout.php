<?php
session_start(); // On démarre la session

// Pour détruire la session :
session_destroy();

// Redirection vers la page de connexion (à adapter)
header('Location: login.php');
exit;
?>