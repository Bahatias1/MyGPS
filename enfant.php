<?php require_once 'menu.php'; ?>

<div class="container-fluid">
    <div class="d-flex justify-content-center">
        <div class="card w-75">
            <div class="card-body">
                <h5 class="card-title fw-semibold mb-4 text-center">Formulaire d'enregistrement</h5>
                <form method="post" enctype="multipart/form-data">
                    <?php
                    // Paramètres de connexion à la base de données
                    $dsn = 'mysql:host=localhost;dbname=localisation';
                    $login = 'root';
                    $password = '';

                    // Connexion à la base de données
                    try {
                        $pdo = new PDO($dsn, $login, $password);
                        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    } catch (PDOException $e) {
                        // Log error instead of displaying it
                        error_log($e->getMessage());
                        echo "<p class='text-danger text-center'>Erreur de connexion à la base de données.</p>";
                        exit();
                    }

                    // Gestion de l'enregistrement
                    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['nom'])) {
                        $nom = htmlspecialchars($_POST['nom']);
                        $postNom = htmlspecialchars($_POST['postNom']);
                        $prenom = htmlspecialchars($_POST['prenom']);
                        $age = (int)$_POST['age']; // Cast to integer
                        $classe = htmlspecialchars($_POST['classe']);
                        $photo = $_FILES['photo']['name'];
                        $uploadDir = "assets/images/";
                        $fileSize = $_FILES['photo']['size'];
                        
                        // File size limit (e.g., 2MB)
                        if ($fileSize > 2000000) {
                            echo "<p class='text-danger text-center'>L'image ne doit pas dépasser 2 Mo.</p>";
                        } else {
                            // Vérification du type de fichier
                            $fileType = pathinfo($photo, PATHINFO_EXTENSION);
                            $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
                            if (in_array($fileType, $allowedTypes) && $_FILES['photo']['error'] == 0) {
                                $newFileName = uniqid() . '.' . $fileType; // Renaming the file
                                move_uploaded_file($_FILES['photo']['tmp_name'], $uploadDir . $newFileName);

                                // Insertion des données dans la base de données
                                $sql = "INSERT INTO enfant (nom, postNom, prenom, age, classe, photo) VALUES (:nom, :postNom, :prenom, :age, :classe, :photo)";
                                $stmt = $pdo->prepare($sql);
                                $stmt->bindParam(':nom', $nom);
                                $stmt->bindParam(':postNom', $postNom);
                                $stmt->bindParam(':prenom', $prenom);
                                $stmt->bindParam(':age', $age);
                                $stmt->bindParam(':classe', $classe);
                                $stmt->bindParam(':photo', $newFileName);
                                $stmt->execute();

                                echo "<p class='text-success text-center'>Données enregistrées avec succès!</p>";
                            } else {
                                echo "<p class='text-danger text-center'>Erreur lors du téléchargement de l'image. Vérifiez le type de fichier.</p>";
                            }
                        }
                    }

                    // Suppression d'un enfant
                    if (isset($_GET['supprimer'])) {
                        $idEnfant = (int)$_GET['supprimer'];
                        $sql = "DELETE FROM enfant WHERE idEnfant = :idEnfant";
                        $stmt = $pdo->prepare($sql);
                        $stmt->bindParam(':idEnfant', $idEnfant);
                        $stmt->execute();
                        echo "<p class='text-success text-center'>Enfant supprimé avec succès!</p>";
                    }

                    // Récupération des informations des enfants
                    $enfants = $pdo->query("SELECT idEnfant, nom, postNom, prenom, age, classe, photo FROM enfant")->fetchAll(PDO::FETCH_ASSOC);
                    ?>

<div class="mb-3">
    <div class="table-responsive">
        <table class="table table-striped table-bordered text-nowrap mb-0">
            <tbody>
                <tr>
                    <td class="text-center"><label for="photo" class="form-label">Photo</label></td>
                    <td>
                        <input type="file" name="photo" class="form-control" id="photo" required>
                    </td>
                </tr>
                <tr>
                    <td class="text-center"><label for="nom" class="form-label">Nom</label></td>
                    <td>
                        <input type="text" name="nom" class="form-control" id="nom" required>
                    </td>
                </tr>
                <tr>
                    <td class="text-center"><label for="postNom" class="form-label">Post Nom</label></td>
                    <td>
                        <input type="text" name="postNom" class="form-control" id="postNom">
                    </td>
                </tr>
                <tr>
                    <td class="text-center"><label for="prenom" class="form-label">Prénom</label></td>
                    <td>
                        <input type="text" name="prenom" class="form-control" id="prenom" required>
                    </td>
                </tr>
                <tr>
                    <td class="text-center"><label for="age" class="form-label">Âge</label></td>
                    <td>
                        <input type="number" name="age" class="form-control" id="age" required>
                    </td>
                </tr>
                <tr>
                    <td class="text-center"><label for="classe" class="form-label">Classe</label></td>
                    <td>
                        <input type="text" name="classe" class="form-control" id="classe" required>
                    </td>
                </tr>
                <tr>
                    <td colspan="2" class="text-center">
                        <button type="submit" class="btn btn-primary">Save</button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

                </form>
            </div>
        </div>
    </div>

    <div class="card mt-5 w-75 mx-auto">
        <div class="card-body">
            <h5 class="card-title fw-semibold mb-4 text-center">Enfants enregistrés</h5>
            <div class="table-responsive">
                <table class="table text-nowrap mb-0 align-middle">
                    <thead class="text-dark fs-4">
                        <tr class="text-center">
                            <th>Id</th>
                            <th>Nom</th>
                            <th>Post Nom</th>
                            <th>Prénom</th>
                            <th>Âge</th>
                            <th>Classe</th>
                            <th>Photo</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($enfants)): ?>
                            <?php foreach ($enfants as $enfant): ?>
                                <tr class="text-center">
                                    <td><?php echo htmlspecialchars($enfant['idEnfant']); ?></td>
                                    <td><?php echo htmlspecialchars($enfant['nom']); ?></td>
                                    <td><?php echo htmlspecialchars($enfant['postNom']); ?></td>
                                    <td><?php echo htmlspecialchars($enfant['prenom']); ?></td>
                                    <td><?php echo htmlspecialchars($enfant['age']); ?></td>
                                    <td><?php echo htmlspecialchars($enfant['classe']); ?></td>
                                    <td><img src="assets/images/<?php echo htmlspecialchars($enfant['photo']); ?>" alt="Photo de l'enfant" width="50"></td>
                                    <td><a href="?supprimer=<?php echo htmlspecialchars($enfant['idEnfant']); ?>" class="btn btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet enfant ?');">Supprimer</a></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center">Aucun enfant enregistré pour le moment.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>
