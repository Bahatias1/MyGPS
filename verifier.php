
<?php
session_start();

$dsn = 'mysql:host=localhost;dbname=localisation';
$login = 'root';
$password = '';

try {
    $pdo = new PDO($dsn, $login, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER["REQUEST_METHOD"] == "GET") {
        $email = $_GET["email"];
        $motDePasse = $_GET["motDePasse"];

        $sql = "SELECT motDePass FROM utilisateurs WHERE email = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

       
        if ($user) {
            // echo "<p style='text-align:center;'>Mot de passe saisi : " . htmlspecialchars($motDePasse) . "</p>";
            // echo "<p style='text-align:center;'>Mot de passe haché : " . htmlspecialchars($user['motDePass']) . "</p>";
        

            echo "<p style='text-align:center; color:green;'>votre mot de passe a ete retrouve</p>";
            $_GET["email"] = $user['motDePass'];
           
        }else{
            echo "<p style='text-align:center; color:red;'>votre adresse mail est introuvable impossible de restorer le mot de passe</p>";
        }
        header("Location:autentification.php");
    }
} catch (PDOException $e) {
    echo "<p style='text-align:center; color:red;'>Erreur de connexion à la base de données : " . $e->getMessage() . "</p>";
}
?>