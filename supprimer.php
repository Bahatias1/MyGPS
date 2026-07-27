<?php
$dsn = 'mysql:host=localhost;dbname=localisation';
$login = 'root';
$password = '';

try {
    $pdo = new PDO($dsn, $login, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['effacer'])) {
        $idEnfant = htmlspecialchars($_POST['idEnfant']);

        $rqt = "DELETE FROM enfant WHERE idEnfant = :idEnfant";
        $stm = $pdo->prepare($rqt);
        $stm->bindParam(':idEnfant', $idEnfant, PDO::PARAM_INT);

        if ($stm->execute()) {
            echo "<script>showSuccessMessage('Vous avez supprimé l\'enfant numéro " . htmlspecialchars($idEnfant) . " avec succès.');</script>";
           header("location:afficherEnfants.php");
        } else {
            echo "<p style='text-align:center; color:red;'>Erreur lors de la suppression de l'enfant.</p>";
        }
    }
} catch (PDOException $e) {
    echo "<p style='text-align:center; color:red;'>Erreur : " . $e->getMessage() . "</p>";
}
?>
