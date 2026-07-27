<?php
$dsn = 'mysql:host=localhost;dbname=localisation';
$login = 'root';
$password = '';
$id = intval($_GET['id']);

try {
    $pdo = new PDO($dsn, $login, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Récupérer les informations de l'enfant
    $stmtEnfant = $pdo->prepare("SELECT * FROM enfant WHERE idEnfant = :id");
    $stmtEnfant->execute(['id' => $id]);
    $enfant = $stmtEnfant->fetch(PDO::FETCH_ASSOC);

    // Récupérer les positions
    $stmtPosition = $pdo->prepare("SELECT * FROM position WHERE idEnfant = :id ORDER BY id ASC");
    $stmtPosition->execute(['id' => $id]);
    $positions = $stmtPosition->fetchAll(PDO::FETCH_OBJ);

} catch (PDOException $e) {
    echo "Erreur de connexion : " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Carte de Goma, RDC</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <style>
        #map {
            height: 500px;
            width: 100%;
        }
    </style>
</head>
<body>
    
    <h1>Détails de l'enfant</h1>
    <?php if ($enfant): ?>
        <div>
            <h3>Nom : <?= htmlspecialchars($enfant['nom']) ?></h3>
            <h3>Prénom : <?= htmlspecialchars($enfant['prenom']) ?></h3>
            <h3>Post Nom : <?= htmlspecialchars($enfant['postNom']) ?></h3>
            <h3>Classe : <?= htmlspecialchars($enfant['classe']) ?></h3>
            <h3>Photo :</h3>
            <?php if (file_exists("assets/images/" . $enfant['photo'])): ?>
                <img src="assets/images/<?= htmlspecialchars($enfant['photo']) ?>" alt="Photo de <?= htmlspecialchars($enfant['prenom']) ?>" style="width: 150px; height: auto;">
            <?php else: ?>
                <img src="assets/images/default.jpg" alt="Image non disponible" style="width: 150px; height: auto;">
            <?php endif; ?>
        </div>
    <?php else: ?>
        <p>Enfant non trouvé.</p>
    <?php endif; ?>

    <div id="map"></div>

    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <script>
        // Initialiser la carte centrée sur Goma, RDC
        var map = L.map('map').setView([-1.6585, 29.2203], 12); // Coordonnées de Goma

        // Ajouter une couche de tuiles (OpenStreetMap)
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 500,
            attribution: '© OpenStreetMap'
        }).addTo(map);

        <?php foreach($positions as $position): ?>
        // Ajouter un marqueur à chaque position
        L.marker([<?= $position->latitude ?>, <?= $position->longitude ?>]).addTo(map)
            .bindPopup('Latitude: <?= $position->latitude ?>, Longitude: <?= $position->longitude ?>')
            .openPopup();
        <?php endforeach; ?>
    </script>
</body>
</html>
