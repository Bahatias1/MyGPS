<?php
$dsn = 'mysql:host=localhost;dbname=localisation';
$login = 'root';
$password = '';

try {
    $pdo = new PDO($dsn, $login, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = "SELECT * FROM enfant";
    $stmt = $pdo->query($sql);
    $devices = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Erreur de connexion : " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Affichage des Enfants</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header>
    <h2><strong>SYSLOC</strong></h2>
    <a href="enregistrerEnfant.php">Ajouter</a>
    <a href="afficherEnfants.php">Afficher</a>
    <!-- <a href="suivreEnfant.php">Suivre</a> -->
    <a href="autentification.php">Déconnexion</a>
</header>

<div class="formEn">
    <h2 style="text-align: center;">Liste des enfants enregistrés</h2>
    <div class="table-container">
        <table class="tableAff" style="padding: auto; margin:auto; border-collapse: collapse; padding: 80px;" table border="1px">
            <thead>
                <tr class="mesth">
                    <th>ID</th>
                    <th>Nom</th>
                    <th>Post-Nom</th>
                    <th>Prénom</th>
                    <th>Age</th>
                    <th>Classe</th>
                    <th>Photo</th>
                    <th>Operation</th>
                    
                </tr>
                
            </thead>
            <tbody>
                <?php foreach ($devices as $device): ?>
                    <tr class="mestd">
                        <td><?php echo htmlspecialchars($device['idEnfant']); ?></td>
                        <td><?php echo htmlspecialchars($device['nom']); ?></td>
                        <td><?php echo htmlspecialchars($device['postNom']); ?></td>
                        <td><?php echo htmlspecialchars($device['prenom']); ?></td>
                        <td><?php echo htmlspecialchars($device['age']); ?></td>
                        <td><?php echo htmlspecialchars($device['classe']); ?></td>
                        <td><img style = "width : 60px; height : 60px; "src="./photos/<?php echo $device['photo']?>"></td>
                        <td>
                                <div class="operation">
                                    <form action="suivreEnfant.php" method="POST">
                                        <input type="hidden" name="idEnfant" value="<?php echo htmlspecialchars($device['idEnfant']);?>">
                                        <a href="lacaliser.php?id=<?=$device['idEnfant']?>" style="background-color: rgb(109, 11, 44); color: white; border-radius: 5px; padding:1px; text-decoration: none; display: inline-block;">suivre
                                             
                                        </a>

                                    </form>
                                    <form action="supprimer.php" method="POST">
                                        <input type="hidden" name="idEnfant" value="<?php echo htmlspecialchars($device['idEnfant']); ?>">
                                        <input type="submit" value="Effacer" name="effacer" onclick="return confirmDeletion();" style="background-color: rgb(109, 11, 44); color:white;">
                                    </form>
                                    <script>
                                            function confirmDeletion() {
                                            return confirm("Êtes-vous sûr de vouloir supprimer les informstions cet  enfant de la table de la table ?");
                                              }
                                              
                                            function showSuccessMessage(message) {
                                                alert(message);
                                            }
                                        
                                    </script>
                                    <form action="modifier.php" method="POST">
                                        <input type="hidden" name="idEnfant" value="<?php echo htmlspecialchars($device['idEnfant']); ?>">
                                        <input type="submit" value="Modifier"  name="modifier" style="background-color: rgb(109, 11, 44); color:white;">
                                    </form>
                                    <!-- <form action="etat.php" method="POST">
                                        <input type="hidden" name="idEnfant" value="<?php
                                        // echo htmlspecialchars($device['idEnfant']); 
                                         ?>">
                                        <input type="submit" value="alerter"  name="etat" style="background-color: rgb(109, 11, 44); color:white;">
                                    </form> -->
                                </div>
                        </td>
                        
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
