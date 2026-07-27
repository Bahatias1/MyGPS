<!-- la page pour l'autentification -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Authentification</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class="formEng">
        <h2 style="text-align: center; padding: 2%;">Authentification</h2>
        <form action="" method="post">
            <table style="padding: 3%; margin-left: 29%;">
                <tr>
                    <td><label for="email"><strong>Email</strong></label></td>
                    <td><input type="email" name="email" required></td>
                </tr>
                <tr>
                    <td><label for="motDePasse"><strong>Mot de passe</strong></label></td>
                    <td><input type="password" name="motDePasse" required></td>
                </tr>
            </table>
            <div class="enreg" style="margin-left: 45%;">
                <input type="submit" name="submit" value="connecter">
            </div>
        </form>
        <p style="color: black; text-align:center;"><a href="verifier.php">mot de passe oublier? </a></p> 
        <p style="color: black; text-align:center;">vous n'avez pas encore de compte? <a href="utilisateur.php"> inscrivez-vous</a></p>
                    
    </div>
    <?php
    session_start();

    $dsn = 'mysql:host=localhost;dbname=localisation';
    $login = 'root';
    $password = '';

    try {
        $pdo = new PDO($dsn, $login, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $email = $_POST["email"];
            $motDePasse = $_POST["motDePasse"];

            $sql = "SELECT id, prenom, nom, motDePass FROM utilisateurs WHERE email = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                // echo "<p style='text-align:center;'>Mot de passe saisi : " . htmlspecialchars($motDePasse) . "</p>";
                // echo "<p style='text-align:center;'>Mot de passe haché : " . htmlspecialchars($user['motDePass']) . "</p>";
            
                if ($motDePasse==$user['motDePass']) {
                    echo "<p style='text-align:center; color:green;'>Mot de passe correct</p>";
                    $_SESSION['email'] = $user['id'];
                    header("Location: enregistrerEnfant.php");
                    exit();
                } else {
                    echo "<p style='text-align:center; color:red;'>Mot de passe incorrect</p>";
                }
            }
        }
    } catch (PDOException $e) {
        echo "<p style='text-align:center; color:red;'>Erreur de connexion à la base de données : " . $e->getMessage() . "</p>";
    }
    ?>
</body>
</html>
