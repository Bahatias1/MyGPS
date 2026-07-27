<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php
    // Paramètres de connexion à la base de données
    $dsn = 'mysql:host=localhost;dbname=localisation';
    $login = 'root';
    $password = '';

    // Connexion à la base de données
    try {
        $pdo = new PDO($dsn, $login, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['nom'])) {
            // Récupération des données POST et sécurisation
            $nom = htmlspecialchars($_POST['nom']);
            $postNom = htmlspecialchars($_POST['postNom']);
            $prenom = htmlspecialchars($_POST['prenom']);
            $age = htmlspecialchars($_POST['age']);
            $classe = htmlspecialchars($_POST['classe']);
            $photo = $_FILES['photo']['name'];
            move_uploaded_file($_FILES['photo']['tmp_name'], "../photos/" . $_FILES['photo']['name']);

            // Insertion des données dans la base de données
            $sql = "INSERT INTO enfant (nom, postNom, prenom, age, classe, photo) VALUES (:nom, :postNom, :prenom, :age, :classe, :photo)";
            $stmt = $pdo->prepare($sql);

            // Liaison des paramètres
            $stmt->bindParam(':nom', $nom);
            $stmt->bindParam(':postNom', $postNom);
            $stmt->bindParam(':prenom', $prenom);
            $stmt->bindParam(':age', $age);
            $stmt->bindParam(':classe', $classe);
            $stmt->bindParam(':photo', $photo);

            // Exécution de la requête
            $stmt->execute();

            echo "<p class='success-message'>Données enregistrées avec succès!</p>";
            header("Location: afficherEnfants.php");
            exit;
        }

        // Récupération des informations des enfants pour le select
        $enfants = $pdo->query("SELECT idEnfant, nom, postNom, prenom, age, classe, photo FROM enfant")->fetchAll(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        echo "<p class='error-message'>Erreur : " . $e->getMessage() . "</p>";
    }
?>
<header>
    <h2><strong>SYSLOC</strong></h2>
    <a href="enregistrerEnfant.php">Ajouter</a>
    <a href="afficherEnfants.php">Afficher</a>
    <a href="autentification.php">Déconnexion</a>
</header>
    
<div class="formEng">
    <h2>Les informations de l'enfant</h2>
    <form action="" method="post" enctype="multipart/form-data">
        <table>
            <tr>
                <td><label for="nom"><strong>Nom</strong></label></td>
                <td><input type="text" name="nom" required></td>
            </tr>
            <tr>
                <td><label for="postNom"><strong>Post-Nom</strong></label></td>
                <td><input type="text" name="postNom" required></td>
            </tr>
            <tr>
                <td><label for="prenom"><strong>Prénom</strong></label></td>
                <td><input type="text" name="prenom" required></td>
            </tr>
            <tr>
                <td><label for="age"><strong>Age</strong></label></td>
                <td><input type="number" name="age" required></td>
            </tr>
            <tr>
                <td><label for="classe"><strong>Classe</strong></label></td>
                <td>
                    <select name="classe" id="">
                        <option value="première maternel">Première maternel</option>
                        <option value="deuxième maternel">Deuxième maternel</option>
                        <option value="troisième maternel">Troisième maternel</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td><label for="photo"><strong>Photo</strong></label></td>
                <td><input type="file" name="photo" required></td>
            </tr>
        </table>
        <div class="submit-btn">
            <input type="submit" name="submit" value="Enregistrer">
        </div>
    </form>
</div>
    
</body>
</html>
