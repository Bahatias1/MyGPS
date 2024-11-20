<?php
// Vérifiez si la session est déjà active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Vérification de la session utilisateur (admin ou enseignant)
if (!isset($_SESSION['id'])) { // Vérifiez que l'ID de l'utilisateur est dans la session
    header('Location: login.php');
    exit();
}

// Connexion à la base de données
$dsn = 'mysql:host=localhost;dbname=localisation';
$login = 'root';
$password = '';

try {
    // Connexion PDO à la base de données
    $pdo = new PDO($dsn, $login, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo 'Error: ' . htmlspecialchars($e->getMessage());
    exit();
}

// Initialisation de la variable de recherche
$searchResults = [];
$searchTerm = '';

// Récupérer l'idEnfant de la session pour les notifications
$idEnfant = $_SESSION['idEnfant'] ?? null;

// Requête pour compter les notifications non lues
if ($idEnfant) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE is_read = 0 AND idEnfant = :idEnfant");
    $stmt->execute(['idEnfant' => $idEnfant]); // Utilisez l'ID de l'enfant pour les notifications
    $unreadCount = $stmt->fetchColumn(); // Récupère le nombre de notifications non lues
} else {
    $unreadCount = 0; // Si idEnfant n'est pas défini
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['search'])) {
    $searchTerm = trim($_POST['search']);

    // Requête pour rechercher les enfants dans la base de données
    $sql = "SELECT * FROM enfant WHERE prenom LIKE :searchTerm OR nom LIKE :searchTerm OR postNom LIKE :searchTerm"; // Recherche par prénom, nom ou postNom
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['searchTerm' => "%$searchTerm%"]);

    $searchResults = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>My GPS</title>
    <link rel="shortcut icon" type="image/png" href="assets/images/logos/favicon.png" />
    <link rel="stylesheet" href="assets/css/styles.min.css" />
</head>
<body>
    <!-- Body Wrapper -->
    <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full" data-sidebar-position="fixed" data-header-position="fixed">

        <!-- Sidebar Start -->
        <aside class="left-sidebar">
            <div>
                <div class="brand-logo d-flex align-items-center justify-content-between">
                    <a class="navbar-brand" href="#">
                        <h1><strong>My GPS</strong></h1>
                    </a>
                    <a href="./index.php" class="text-nowrap logo-img"></a>
                    <div class="close-btn d-xl-none d-block sidebartoggler cursor-pointer" id="sidebarCollapse">
                        <i class="ti ti-x fs-8"></i>
                    </div>
                </div>

                <!-- Sidebar navigation -->
                <nav class="sidebar-nav scroll-sidebar" data-simplebar="">
                    <ul id="sidebarnav">
                        <li class="nav-small-cap">
                            <i class="ti ti-dots nav-small-cap-icon fs-4"></i>
                            <span class="hide-menu">Home</span>
                        </li>
                        <li class="sidebar-item">
                            <a class="sidebar-link" href="./index.php" aria-expanded="false">
                                <span><i class="ti ti-layout-dashboard"></i></span>
                                <span class="hide-menu">Dashboard</span>
                            </a>
                        </li>
                        <li class="nav-small-cap">
                            <i class="ti ti-dots nav-small-cap-icon fs-4"></i>
                            <span class="hide-menu">MENU</span>
                        </li>
                        <li class="sidebar-item">
                            <a class="sidebar-link" href="enfant.php" aria-expanded="false">
                                <span><i class="ti ti-user-plus"></i></span>
                                <span class="hide-menu">Add Students</span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a class="sidebar-link" href="EafficherEnfants.php" aria-expanded="false">
                                <span><i class="ti ti-alert-circle"></i></span>
                                <span class="hide-menu">Student</span>
                            </a>
                        </li>
                    </ul>
                </nav>
                <!-- End Sidebar navigation -->
            </div>
        </aside>
        <!-- Sidebar End -->

        <!-- Main wrapper -->
        <div class="body-wrapper">

            <!-- Header -->
            <header class="app-header">
                <nav class="navbar navbar-expand-lg navbar-light">
                    <form method="post" action="">
                        <div class="input-group">
                            <div class="form-outline" data-mdb-input-init>
                                <input type="search" name="search" id="form1" class="form-control" placeholder="Search student" aria-label="Search" value="<?php echo htmlspecialchars($searchTerm); ?>" />
                            </div>
                            <button type="submit" class="btn btn-primary" data-mdb-ripple-init>
                                <i class="ti ti-search"></i>
                            </button>
                        </div>
                    </form>

                    <div class="navbar-collapse justify-content-end px-0" id="navbarNav">
                        <ul class="navbar-nav flex-row ms-auto align-items-center justify-content-end">
                            <!-- Notification Icon -->
                            <li class="nav-item dropdown">
                                <a class="nav-link nav-icon-hover" href="javascript:void(0)" id="notificationDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="ti ti-bell fs-5"></i>
                                    <span class="badge bg-danger rounded-circle"><?php echo $unreadCount; ?></span> <!-- Badge de notification -->
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end p-0" aria-labelledby="notificationDropdown">
                                    <li><a class="dropdown-item" href="#">New notification 1</a></li>
                                    <li><a class="dropdown-item" href="#">New notification 2</a></li>
                                    <li><a class="dropdown-item" href="#">New notification 3</a></li>
                                </ul>
                            </li>

                            <!-- Logout Button -->
                            <a href="logout.php" class="btn btn-primary ms-2">Logout</a>
                            
                            <!-- Profile Icon -->
                            <li class="nav-item dropdown ms-3">
                                <a class="nav-link nav-icon-hover" href="javascript:void(0)" id="drop2" data-bs-toggle="dropdown" aria-expanded="false">
                                    <img src="assets/images/profile/user-1.jpg" alt="" width="35" height="35" class="rounded-circle">
                                </a>
                            </li>
                        </ul>
                    </div>
                </nav>
            </header>
            <!-- End Header -->

            <!-- Main Content -->
<div class="container-fluid">
    <?php if ($searchResults): // Vérifie s'il y a des résultats ?>
        <h4 class="mt-3">Search Results</h4> <!-- Titre affiché uniquement s'il y a des résultats -->
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th> <!-- Colonne ID -->
                    <th>Prenom</th>
                    <th>Nom</th>
                    <th>Post-Nom</th>
                    <th>Âge</th> <!-- Colonne Age -->
                    <th>Classe</th> <!-- Colonne Classe -->
                    <th>Photo</th> <!-- Colonne Photo -->
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($searchResults as $result): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($result['idEnfant']); ?></td> <!-- Affichage de l'ID -->
                        <td><?php echo htmlspecialchars($result['prenom']); ?></td>
                        <td><?php echo htmlspecialchars($result['nom']); ?></td>
                        <td><?php echo htmlspecialchars($result['postNom']); ?></td>
                        <td><?php echo htmlspecialchars($result['age']); ?></td> <!-- Affichage de l'Age -->
                        <td><?php echo htmlspecialchars($result['classe']); ?></td> <!-- Affichage de la Classe -->
                        <td>
                        <?php if (!empty($result['photo'])): ?>
                            <img src="assets/images/<?php echo htmlspecialchars($result['photo']); ?>" alt="Photo" width="50" height="50">
                        <?php else: ?>
                            No Photo
                        <?php endif; ?>
                        </td> <!-- Affichage de la Photo -->
                        <td>
                            <a href="modifierEnfant.php?id=<?php echo $result['idEnfant']; ?>" class="btn btn-warning btn-sm">Modifier</a>
                            <form method="post" action="supprimerEnfant.php" style="display:inline;">
                                <input type="hidden" name="id" value="<?php echo $result['idEnfant']; ?>">
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: // Aucune résultat trouvé ?>
        <?php if (!empty($searchTerm)): // Vérifie si une recherche a été effectuée ?>
            <p class="mt-3">Aucun résultat trouvé pour "<strong><?php echo htmlspecialchars($searchTerm); ?></strong>"</p>
        <?php endif; ?>
    <?php endif; ?>
</div>

    </div>
    <script src="assets/js/scripts.min.js"></script>
</body>
</html>
