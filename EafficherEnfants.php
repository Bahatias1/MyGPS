<?php
$dsn = 'mysql:host=localhost;dbname=localisation';
$login = 'root';
$password = '';
try {
    $pdo = new PDO($dsn, $login, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Could not connect to the database: " . $e->getMessage());
}

$devices = []; // Initialisation de la variable $devices

// Récupérer tous les enfants s'il n'y a pas de recherche
if (!isset($_POST['search']) || empty($_POST['search'])) {
    $sql = $pdo->query("SELECT * FROM enfant");
    $devices = $sql->fetchAll(PDO::FETCH_ASSOC);
} else {
    // Protection contre les injections SQL
    $search = htmlspecialchars($_POST['search']);
    $sql = $pdo->prepare("SELECT * FROM enfant WHERE nom LIKE :search");
    $sql->execute(['search' => '%' . $search . '%']);
    $devices = $sql->fetchAll(PDO::FETCH_ASSOC);
}

require_once 'menu.php'; // Inclure le menu
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-lg-15">
            <div class="card overflow-hidden">
                <div class="card-body">
                    <h5 class="card-title fw-semibold mb-4">Table des Enfants</h5>
                    <div class="table-responsive">
                    <table class="table table-striped table-bordered text-nowrap mb-0 align-middle">
    <thead class="text-dark fs-4">
        <tr>
            <th class="border-bottom-0"><h6 class="fw-semibold mb-0">Id</h6></th>
            <th class="border-bottom-0"><h6 class="fw-semibold mb-0">Nom</h6></th>
            <th class="border-bottom-0"><h6 class="fw-semibold mb-0">Post Nom</h6></th>
            <th class="border-bottom-0"><h6 class="fw-semibold mb-0">Prénom</h6></th>
            <th class="border-bottom-0"><h6 class="fw-semibold mb-0">Classe</h6></th>
            <th class="border-bottom-0"><h6 class="fw-semibold mb-0">Action</h6></th>
        </tr>
    </thead>
    <tbody>

    </tbody>


                                <?php if (!empty($devices)): ?>
                                    <?php foreach ($devices as $device): ?>
                                        <tr>
                                            <td class="border-bottom-0">
                                                <h6 class="fw-semibold mb-0"><?php echo htmlspecialchars($device['idEnfant']); ?></h6>
                                            </td>
                                            <td class="border-bottom-0">
                                                <h6 class="fw-semibold mb-1"><?php echo htmlspecialchars($device['nom']); ?></h6>
                                            </td>
                                            <td class="border-bottom-0">
                                                <p class="mb-0 fw-normal"><?php echo htmlspecialchars($device['postNom']); ?></p>
                                            </td>
                                            <td class="border-bottom-0">
                                                <h6 class="fw-semibold mb-0 fs-4"><?php echo htmlspecialchars($device['prenom']); ?></h6>
                                            </td>
                                            <td class="border-bottom-0">
                                                <span class="badge bg-secondary rounded-3 fw-semibold"><?php echo htmlspecialchars($device['classe']); ?></span>
                                            </td>
                                            <td class="border-bottom-0">
                                                <a href="localiser.php?id=<?php echo htmlspecialchars($device['idEnfant']); ?>" class="btn btn-primary">Suivre l'élève</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center">Aucun enfant trouvé.</td>
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

<?php require_once 'footer.php'; // Inclure le pied de page ?>
