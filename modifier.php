<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>mofification</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php
$dsn = 'mysql:host=localhost;dbname=localisation';
$login = 'root';
$password = '';

try {
    $pdo = new PDO($dsn, $login, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Vérification si un ID a été passé pour la modification
    if (isset($_POST['idEnfant'])) {
        $idEnfant = htmlspecialchars($_POST['idEnfant']);
        
        // Récupération des données actuelles de l'enfant
        $sql = "SELECT * FROM enfant WHERE idEnfant = :idEnfant";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':idEnfant', $idEnfant, PDO::PARAM_INT);
        $stmt->execute();
        $enfant = $stmt->fetch(PDO::FETCH_ASSOC);

        // Si l'enfant n'existe pas
        if (!$enfant) {
            echo "<p style='text-align:center; color:red;'>Aucun enfant trouvé avec cet ID.</p>";
            exit;
        }
    } else {
        echo "<p style='text-align:center; color:red;'>ID de l'enfant non fourni.</p>";
        exit;
    }

    // Vérification si le formulaire a été soumis pour la mise à jour
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['nom'])) {
        $nom = htmlspecialchars($_POST['nom']);
        $postNom = htmlspecialchars($_POST['postNom']);
        $prenom = htmlspecialchars($_POST['prenom']);
        $age = htmlspecialchars($_POST['age']);
        $classe = htmlspecialchars($_POST['classe']);
        $photo = $_FILES['photo']['name'];
        move_uploaded_file($_FILES['photo']['tmp_name'],"../photos/".$_FILES['photo']['name']);

        // Mise à jour des données de l'enfant dans la base de données
        $sql = "UPDATE enfant SET nom = :nom, postNom = :postNom, prenom = :prenom, age = :age, classe = :classe, photo = :photo WHERE idEnfant = :idEnfant";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':nom', $nom);
        $stmt->bindParam(':postNom', $postNom);
        $stmt->bindParam(':prenom', $prenom);
        $stmt->bindParam(':age', $age);
        $stmt->bindParam(':classe', $classe);
        $stmt->bindParam(':photo',$photo);
        $stmt->bindParam(':idEnfant', $_POST['idEnfant'], PDO::PARAM_INT);

        // Exécution de la requête de mise à jour
        if ($stmt->execute()) {
            echo "<p style='text-align:center; color:green;'>Données modifiées avec succès!</p>";
            header("Location: afficherEnfants.php");
            exit;
        } else {
            echo "<p style='text-align:center; color:red;'>Erreur lors de la mise à jour des données.</p>";
        }
    }
} catch (PDOException $e) {
    echo "<p style='text-align:center; color:red;'>Erreur : " . $e->getMessage() . "</p>";
}
?>

<header>
    <h2><strong>SYSLOC</strong></h2>
    <a href="enregistrerEnfant.php">Ajouter</a>
    <a href="afficherEnfants.php">Afficher</a>
    <a href="autentification.php">Déconnexion</a>
</header>
    
<div class="formEng">
    <h2 style="text-align: center; margin:auto; padding:auto; margin-top:3%;">Modifier les informations de l'enfant</h2><br>
    <form action="" method="post" enctype="multipart/form-data">
        <input type="hidden" name="idEnfant" value="<?php echo htmlspecialchars($enfant['idEnfant']); ?>">
        <table style="margin:auto; padding:auto;">
            <tr>
                <td><label for="nom"><strong>Nom</strong></label></td>
                <td><input type="text" name="nom" value="<?php echo htmlspecialchars($enfant['nom']); ?>" required></td>
            </tr>
            <tr>
                <td><label for="postNom"><strong>Post-Nom</strong></label></td>
                <td><input type="text" name="postNom" value="<?php echo htmlspecialchars($enfant['postNom']); ?>" required></td>
            </tr>
            <tr>
                <td><label for="prenom"><strong>Prénom</strong></label></td>
                <td><input type="text" name="prenom" value="<?php echo htmlspecialchars($enfant['prenom']); ?>" required></td>
            </tr>
            <tr>
                <td><label for="age"><strong>Age</strong></label></td>
                <td><input type="number" name="age" value="<?php echo htmlspecialchars($enfant['age']); ?>" required></td>
            </tr>
            <tr>
                <td><label for="classe"><strong>Classe</strong></label></td>
                <td><input type="text" name="classe" value="<?php echo htmlspecialchars($enfant['classe']); ?>" required></td>
            </tr>
            <tr>
                <td><label for="photo"><strong>photo</strong></label></td>
                <td ><input type="file" name="photo" value="<?php echo htmlspecialchars($enfant['photo']); ?>" style="height: 20px; width: 55%;" required><br></td>
            </tr>
        </table>
        <div class="enreg" style="margin-left: 45%;"><br>
            <input type="submit" name="submit" value="Modifier">
        </div>
    </form>
</div>

</body>
</html>
