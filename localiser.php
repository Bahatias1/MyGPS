<?php
// Connexion à la base de données
$dsn = 'mysql:host=localhost;dbname=localisation';
$login = 'root';
$password = '';

try {
    $pdo = new PDO($dsn, $login, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Récupération des données de position dans la table "position"
    $sql = "SELECT * FROM position ORDER BY id ASC";
    $stmt = $pdo->query($sql);
    $positions = $stmt->fetchAll(PDO::FETCH_OBJ);
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

    <div id="map"></div>

    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <script>
        // Initialiser la carte centrée sur Goma, RDC
        var map = L.map('map').setView([-1.6585, 29.2203], 13); // Coordonnées de Goma

        // Ajouter une couche de tuiles (OpenStreetMap)
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 18,
            attribution: '© OpenStreetMap'
        }).addTo(map);

        // Ajouter les marqueurs pour chaque position récupérée
        var positions = <?php echo json_encode($positions); ?>; // Transmettre les données PHP à JavaScript

        positions.forEach(function(position) {
            // Vérifier si la latitude et la longitude sont valides
            if (position.latitude && position.longitude) {
                L.marker([position.latitude, position.longitude]).addTo(map)
                    .bindPopup('Latitude: ' + position.latitude + '<br>Longitude: ' + position.longitude);
            }
        });
    </script>

</body>
</html>
