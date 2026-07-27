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
        var map = L.map('map').setView([-1.6585, 29.2203], 15);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '© OpenStreetMap'
        }).addTo(map);

        var positions = <?= json_encode($positions) ?>;
        if (positions && positions.length > 0) {
            var latLngs = positions.map(function(p) {
                return [parseFloat(p.latitude), parseFloat(p.longitude)];
            });

            // Tracer la ligne de parcours
            L.polyline(latLngs, { color: '#2563eb', weight: 4, opacity: 0.7, dashArray: '8, 8' }).addTo(map);

            // Afficher le marqueur unique de la position actuelle (dernière reçue)
            var lastPos = positions[positions.length - 1];
            var currentLatLng = [parseFloat(lastPos.latitude), parseFloat(lastPos.longitude)];
            var isAlert = parseInt(lastPos.etat) === 1;

            var marker = L.marker(currentLatLng).addTo(map);
            marker.bindPopup(
                '<strong>📍 Position Actuelle</strong><br>' +
                'Latitude: ' + lastPos.latitude + '<br>Longitude: ' + lastPos.longitude + '<br>' +
                'Statut: ' + (isAlert ? '<span style="color:red;font-weight:bold;">⚠️ DANGER / SOS</span>' : '<span style="color:green;">🟢 Normal</span>')
            ).openPopup();

            map.setView(currentLatLng, 16);
        }
    </script>
</body>
</html>
