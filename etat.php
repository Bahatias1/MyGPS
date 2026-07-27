<!-- fichier de changement des etats des appareils dans la base des données -->
<?php
// header('Content-Type: application/json');
  // Paramètres de connexion à la base de données
$dsn = 'mysql:host=localhost;dbname=energie';
$login = 'root';
$password = '';
  // Connexion à la base de données
try {
    $pdo = new PDO($dsn, $login, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $id = $_POST['idEnfant'] ?? null;
    $state = $_POST['etat'] ?? null;

    if ($id === null || $state === null) {
        echo json_encode(["message" => "Invalid input data"]);
        exit();
    }

    // Mise à jour de l'état dans la base de données
    $sql = "UPDATE position SET etat = :etat WHERE idEnfant = :idEnfant";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':etat', $state, PDO::PARAM_INT);
    $stmt->bindParam(':idEnfant', $id, PDO::PARAM_INT);

    if ($stmt->execute()) {
        echo "changement reussi";
        // header("Location: etatAppareil.php");
    } else {
        echo json_encode(["message" => "Error updating record"]);
    }
} catch (PDOException $e) {
   
}
?>