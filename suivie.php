<?php
$dsn = 'mysql:host=localhost;dbname=localisation';
$login = 'root';
$password = '';

try {
    $pdo = new PDO($dsn, $login, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $idEnfant = htmlspecialchars($_POST['idEnfant']);

        $rqt = "SELECT idEnfant FROM enfant WHERE idEnfant = ?";
        $stm = $pdo->prepare($rqt);
        $stm->execute([$idEnfant]); // Corrected the variable usage
        $user = $stm->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            echo "<p style='text-align:center; color:green;'>L'enfant numéro " . htmlspecialchars($idEnfant) . "</p>";
        } else {
            echo "<p style='text-align:center; color:red;'>Aucun enfant trouvé avec cet ID.</p>";
        }
    }
} catch (PDOException $e) {
    echo "<p style='text-align:center; color:red;'>Erreur : " . $e->getMessage() . "</p>";
}

