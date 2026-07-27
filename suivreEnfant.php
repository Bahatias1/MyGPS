<?php
$dsn = 'mysql:host=localhost;dbname=localisation';
$login = 'root';
$password = '';

try {
    $pdo = new PDO($dsn, $login, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $idEnfant = htmlspecialchars($_POST['idEnfant']);
        // $latitude = htmlspecialchars($_POST['latitude']);
        // $longitude = htmlspecialchars($_POST['longitude']);

        $rqt = "SELECT idEnfant FROM enfant WHERE idEnfant = ?";
        $stm = $pdo->prepare($rqt);
        $stm->execute([$idEnfant]); // Corrected the variable usage
        $user = $stm->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // echo "<p style='text-align:center; color:green;'>L'enfant numéro " . htmlspecialchars($idEnfant) . "</p>";
        } else {
            echo "<p style='text-align:center; color:red;'>Aucun enfant trouvé avec cet ID.</p>";
        }
    }
} catch (PDOException $e) {
    echo "<p style='text-align:center; color:red;'>Erreur : " . $e->getMessage() . "</p>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header>
    <h2><strong>SYSLOC</strong></h2>
    <a href="enregistrerEnfant.php">Ajouter</a>
    <a href="afficherEnfants.php">Afficher</a>
    <!-- <a href="suivreEnfant.php">Suivre</a> -->
    <a href="autentification.php">Deconexion</a>
</header><br>

    <div class="formEn">
        <h2 style="text-align: center;">localisations</h2>
        <table class="tableAff" style="padding: auto; margin:auto; border-collapse: collapse;border-radius:10px; padding: 80px;" table border="1px">
            <thead>
                <tr class="mesth">
                    <th>idEnfant</th>
                    <th>Latitude</th>
                    <th>Longitude</th>
                    <th>Etat</th>
                </tr>
            </thead>
                <tbody>
                    <?php  ?>
                        <tr class="mestd">
                            
                            <td><?php echo htmlspecialchars($idEnfant);?></td>
                            <td></td>
                            <td></td>
                            <td style="color:grey"><?php echo "de danger ou normal";?></td>
                        </tr>
                    <?php  ?>
                </tbody>
        </table>
    
    </div>

</body>
</html>