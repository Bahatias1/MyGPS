<?php
// Démarrer la session
session_start();

// Vérifiez si l'utilisateur est déjà sur index.php
if (basename($_SERVER['PHP_SELF']) !== 'index.php') {
    header("Location: index.php");
    exit;
}

$dsn = 'mysql:host=localhost;dbname=localisation';
$login = 'root';
$password = '';
try {
    $pdo = new PDO($dsn, $login, $password);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

// Récupérer tous les enfants
$sql = "SELECT * FROM enfant";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$devices = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Compter le nombre d'enfants
$count = $stmt->rowCount();
?>

<?php require_once('menu.php'); ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-lg-15 mb-4">
            <div class="card">
                <div class="card-body">
                    <div class="row align-items-start">
                        <div class="col-8">
                            <h5 class="card-title mb-3 fw-semibold">Nombre d'Élèves</h5>
                            <h4 class="fw-semibold mb-3"><?= $count ?></h4>
                            <div class="d-flex align-items-center pb-1">
                                <span class="me-2 rounded-circle bg-light-danger round-20 d-flex align-items-center justify-content-center">
                                    <i class="ti ti-arrow-down-right text-danger"></i>
                                </span>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="d-flex justify-content-end">
                                <div class="text-white bg-secondary rounded-circle p-3 d-flex align-items-center justify-content-center">
                                    <i class="ti ti-user-plus"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="earning"></div>
            </div>
        </div>

        <div class="col-lg-15">
            <div class="card overflow-hidden">
                <div class="card-body">
                    <h5 class="card-title fw-semibold mb-4">Table des Élèves</h5>
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered text-nowrap mb-0 align-middle">
                            <thead class="text-dark fs-5">
                                <tr>
                                    <th class="text-center border-bottom-0" style="width: 5%;">
                                        <h6 class="fw-semibold mb-0">ID</h6>
                                    </th>
                                    <th class="text-center border-bottom-0" style="width: 20%;">
                                        <h6 class="fw-semibold mb-0">Nom</h6>
                                    </th>
                                    <th class="text-center border-bottom-0" style="width: 20%;">
                                        <h6 class="fw-semibold mb-0">PostNom</h6>
                                    </th>
                                    <th class="text-center border-bottom-0" style="width: 20%;">
                                        <h6 class="fw-semibold mb-0">Prenom</h6>
                                    </th>
                                    <th class="text-center border-bottom-0" style="width: 5%;">
                                        <h6 class="fw-semibold mb-0">Âge</h6>
                                    </th>
                                    <th class="text-center border-bottom-0" style="width: 10%;">
                                        <h6 class="fw-semibold mb-0">Classe</h6>
                                    </th>
                                    <th class="text-center border-bottom-0" style="width: 10%;">
                                        <h6 class="fw-semibold mb-0">Photo</h6>
                                    </th>
                                    <th class="text-center border-bottom-0" style="width: 10%;">
                                        <h6 class="fw-semibold mb-0">Actions</h6>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($devices)): // Vérifie si des résultats d'enfants sont trouvés ?>
                                    <?php foreach ($devices as $device): ?>
                                        <tr>
                                            <td class="text-center border-bottom-0"><h6 class="fw-semibold mb-0"><?= htmlspecialchars($device['idEnfant']) ?></h6></td>
                                            <td class="text-center border-bottom-0"><h6 class="fw-semibold mb-0"><?= htmlspecialchars($device['nom']) ?></h6></td>
                                            <td class="text-center border-bottom-0"><h6 class="fw-semibold mb-0"><?= htmlspecialchars($device['postNom']) ?></h6></td>
                                            <td class="text-center border-bottom-0"><h6 class="fw-semibold mb-0"><?= htmlspecialchars($device['prenom']) ?></h6></td>
                                            <td class="text-center border-bottom-0"><h6 class="fw-semibold mb-0"><?= htmlspecialchars($device['age']) ?></h6></td>
                                            <td class="text-center border-bottom-0"><span class="badge bg-secondary rounded-3 fw-semibold"><?= htmlspecialchars($device['classe']) ?></span></td>
                                            <td class="text-center border-bottom-0">
                                                <?php if (!empty($device['photo'])): ?>
                                                    <img src="assets/images/<?= htmlspecialchars($device['photo']) ?>" alt="Photo" width="50" height="50">
                                                <?php else: ?>
                                                    No Photo
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center border-bottom-0">
                                                <a href="localiser.php?id=<?= htmlspecialchars($device['idEnfant']) ?>" class="btn btn-primary btn-sm">Suivre l'Élève</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="8" class="text-center">Aucun résultat trouvé.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once "footer.php"; ?>
