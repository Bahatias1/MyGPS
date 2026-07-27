<!-- la page pour enregistrer les utilisateurs-->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="formEng">
        <h2 style="text-align: center; padding: 2%;">Complétez vos paramètres</h2>
        <form action="" method="post">
            <table style="padding: 3%; margin-left: 25%;">
                <tr>
                    <td><label for="prenom"><strong>Prénom</strong></label></td>
                    <td><input type="text" name="prenom" required></td>
                </tr>
                <tr>
                    <td><label for="nom"><strong>Nom</strong></label></td>
                    <td><input type="text" name="nom" required></td>
                </tr>
                <tr>
                    <td><label for="email"><strong>Email</strong></label></td>
                    <td><input type="email" name="email" required></td>
                </tr>
                <tr>
                    <td><label for="motDePasse"><strong>Mot de passe</strong></label></td>
                    <td><input type="password" name="motDePasse" required></td>
                </tr>
                <tr>
                    <td><label for="motDePasseConfirme"><strong>Confirmez mot de passe</strong></label></td>
                    <td><input type="password" name="motDePasseConfirme" required></td>
                </tr>
            </table>
            <div class="enreg" style="margin-left: 45%;">
                <input type="submit" name="submit" value="Enregistrer">
            </div>
        </form>
        <p style="color: black; text-align:center;">avez-vous déja un compte? <a href="autentification.php"> connectez-vous</a></p>   
    </div>
    <?php
    // Paramètres de connexion à la base de données
    $dsn = 'mysql:host=localhost;dbname=localisation';
    $login = 'root';
    $password = '';

    // Connexion à la base de données
    try {
        $pdo = new PDO($dsn, $login, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Vérification si le formulaire a été soumis
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            // Récupération des données POST et sécurisation
            $prenom = htmlspecialchars($_POST['prenom']);
            $nom = htmlspecialchars($_POST['nom']);
            $email = htmlspecialchars($_POST['email']);
            $motDePasse = $_POST['motDePasse'];
            $motDePasseConfirme = $_POST['motDePasseConfirme'];

            // Vérification que les mots de passe correspondent
            if ($motDePasse !== $motDePasseConfirme) {
                echo "<p style='text-align:center; color:red;'>Les mots de passe ne correspondent pas.</p>";
                exit();
            }

            // Hachage du mot de passe
            // $motDePasseHash = password_hash($motDePasse, PASSWORD_DEFAULT);

            // Insertion des données dans la base de données
            $sql = "INSERT INTO utilisateurs (prenom, nom, email, motDePass) VALUES (:prenom, :nom, :email, :motDePass)";
            $stmt = $pdo->prepare($sql);

            // Liaison des paramètres
            $stmt->bindParam(':prenom', $prenom);
            $stmt->bindParam(':nom', $nom);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':motDePass', $motDePasse);

            // Exécution de la requête
            $stmt->execute();

            echo "<p style='text-align:center; color:yellow;'>Données enregistrées avec succès!</p>";
            header("Location: autentification.php");
        }
    } catch (PDOException $e) {
        echo "<p style='text-align:center; color:red;'>Erreur : " . $e->getMessage() . "</p>";
    }
    ?>
</body>
</html>
