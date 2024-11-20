<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $notification = $_POST['notification'] ?? '';
    if (!empty($notification)) {
        // Ajouter la notification à la session
        $_SESSION['notifications'][] = $notification;
    }
}
?>
