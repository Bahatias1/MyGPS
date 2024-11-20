<?php require_once 'menu.php'; ?>
<div class="container-fluid">
    <div class="card">
        <div class="card-body">
            <h5 class="card-title fw-semibold mb-4">Modifier Enfant</h5>
            <div class="card">
                <div class="card-body">
                    <?php
                    // Paramètres de connexion à la base de données
                    $dsn = 'mysql:host=localhost;dbname=localisation';
                    $login = 'root';
                    $password = '';

                    // Connexion à la base de données
                    try {
                        $pdo = new PDO($dsn, $login, $password);
                        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                        // Vérification de l'ID de l'enfant
                        if (isset($_GET['id'])) {
                            $idEnfant = (int)$_GET['id'];

                            // Récupération des informations de l'enfant
                            $sql = "SELECT * FROM enfant WHERE idEnfant = :idEnfant";
                            $stmt = $pdo->prepare($sql);
                            $stmt->bindParam(':idEnfant', $idEnfant);
                            $stmt->execute();
                            $enfant = $stmt->fetch(PDO::FETCH_ASSOC);

                            if (!$enfant) {
                                echo "<p class='error-message'>Enfant introuvable.</p>";
                                exit;
                            }
                        }

                        // Traitement du formulaire de modification
                        if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['nom'])) {
                            // Récupération des données POST et sécurisation
                            $nom = htmlspecialchars($_POST['nom']);
                            $postNom = htmlspecialchars($_POST['postNom']);
                            $prenom = htmlspecialchars($_POST['prenom']);
                            $age = htmlspecialchars($_POST['age']);
                            $classe = htmlspecialchars($_POST['classe']);
                            
                            // Gestion de l'upload de la photo
                            if ($_FILES['photo']['error'] == UPLOAD_ERR_NO_FILE) {
                                // Pas de fichier uploadé, on garde l'ancienne photo
                                $photo = $enfant['photo'];
                            } else {
                                // Nouveau fichier uploadé
                                $photo = $_FILES['photo']['name'];
                                move_uploaded_file($_FILES['photo']['tmp_name'], "assets/images/" . $_FILES['photo']['name']);
                            }

                            // Mise à jour des données dans la base de données
                            $sql = "UPDATE enfant SET nom = :nom, postNom = :postNom, prenom = :prenom, age = :age, classe = :classe, photo = :photo WHERE idEnfant = :idEnfant";
                            $stmt = $pdo->prepare($sql);
                            
                            // Liaison des paramètres
                            $stmt->bindParam(':nom', $nom);
                            $stmt->bindParam(':postNom', $postNom);
                            $stmt->bindParam(':prenom', $prenom);
                            $stmt->bindParam(':age', $age);
                            $stmt->bindParam(':classe', $classe);
                            $stmt->bindParam(':photo', $photo);
                            $stmt->bindParam(':idEnfant', $idEnfant);

                            // Exécution de la requête
                            $stmt->execute();

                            echo "<p class='success-message'>Données modifiées avec succès!</p>";
                            // header("Location: afficherEnfants.php");
                            exit;
                        }
                    } catch (PDOException $e) {
                        echo "<p class='error-message'>Erreur : " . $e->getMessage() . "</p>";
                    }
                    ?>

<!-- Formulaire pour modifier les informations de l'enfant -->
<form method="post" enctype="multipart/form-data">
    <div class="mb-3">
        <label for="photo" class="form-label">Picture</label>
        <input type="file" name="photo" class="form-control" id="photo">
        <small>Photo actuelle : <img src="assets/images/<?= htmlspecialchars($enfant['photo']); ?>" alt="Photo actuelle" style="height: 100px;"></small>
    </div>
    <div class="mb-3">
        <label for="nom" class="form-label">Name</label>
        <input type="text" name="nom" class="form-control" id="nom" value="<?= htmlspecialchars($enfant['nom']); ?>" required>
    </div>
    <div class="mb-3">
        <label for="postNom" class="form-label">Middle Name</label>
        <input type="text" name="postNom" class="form-control" id="postNom" value="<?= htmlspecialchars($enfant['postNom']); ?>" required>
    </div>
    <div class="mb-3">
        <label for="prenom" class="form-label">Last Name</label>
        <input type="text" name="prenom" class="form-control" id="prenom" value="<?= htmlspecialchars($enfant['prenom']); ?>" required>
    </div>
    <div class="mb-3">
        <label for="age" class="form-label">Age</label>
        <input type="number" name="age" class="form-control" id="age" value="<?= htmlspecialchars($enfant['age']); ?>" required>
    </div>
    <div class="mb-3">
        <label for="classe" class="form-label">Class</label>
        <input type="text" name="classe" class="form-control" id="classe" value="<?= htmlspecialchars($enfant['classe']); ?>" required>
    </div>
    <button type="submit" class="btn btn-primary">Modifier</button>
</form>
<?php require_once 'footer.php'; ?>
