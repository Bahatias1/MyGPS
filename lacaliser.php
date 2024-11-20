<?php
$dsn = 'mysql:host=localhost;dbname=localisation';
$login = 'root';
$password = '';
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT); // Récupérer l'ID de l'enfant

try {
    $pdo = new PDO($dsn, $login, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Vérification si l'ID est valide
    if (!$id) {
        die('ID de l\'enfant manquant ou invalide.');
    }

    // Récupérer les informations de l'enfant
    $sqlEnfant = "SELECT * FROM enfant WHERE idEnfant = :id";
    $stmtEnfant = $pdo->prepare($sqlEnfant);
    $stmtEnfant->bindParam(':id', $id, PDO::PARAM_INT);
    $stmtEnfant->execute();
    $enfant = $stmtEnfant->fetch(PDO::FETCH_OBJ);

    // Vérification si l'enfant existe
    if (!$enfant) {
        die('Enfant non trouvé.');
    }

    // Récupérer les coordonnées GPS les plus récentes de l'enfant
    $sqlPosition = "SELECT * FROM position WHERE idEnfant = :id ORDER BY timestamp DESC LIMIT 1";
    $stmtPosition = $pdo->prepare($sqlPosition);
    $stmtPosition->bindParam(':id', $id, PDO::PARAM_INT);
    $stmtPosition->execute();
    $position = $stmtPosition->fetch(PDO::FETCH_OBJ);

} catch (PDOException $e) {
    echo "Erreur de connexion : " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Suivi de l'enfant</title>
    <!-- Lien vers Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <style>
        /* Style personnalisé pour la section d'information */
        .info img {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 20px;
        }

        /* Style personnalisé pour la carte */
        #map {
            height: 100%;
            width: 100%;
        }

        /* Ajustements pour les petits écrans */
        @media (max-width: 768px) {
            #map {
                height: 400px; /* Réduire la hauteur de la carte sur mobile */
            }
        }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row vh-100">
        <!-- Section gauche : Identité de l'enfant -->
        <div class="col-lg-4 col-md-12 d-flex flex-column align-items-center justify-content-center bg-light p-4 shadow">
            <h2 class="text-center mb-4">Identité de l'Élève</h2>
            <img src="<?= isset($enfant->photo) ? htmlspecialchars($enfant->photo) : 'default_photo.jpg' ?>" alt="Photo de l'enfant">
            <p><strong>Nom :</strong> <?= htmlspecialchars($enfant->nom) ?> <?= htmlspecialchars($enfant->postNom) ?></p>
            <p><strong>Prénom :</strong> <?= htmlspecialchars($enfant->prenom) ?></p>
            <p><strong>Classe :</strong> <?= htmlspecialchars($enfant->classe) ?></p>
        </div>

        <!-- Section droite : Carte de localisation -->
        <div class="col-lg-8 col-md-12 p-0">
            <div id="map"></div>
        </div>
    </div>
</div>

<!-- Lien vers Bootstrap JS et Leaflet JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<script>
    // Initialiser la carte centrée sur la dernière position connue ou par défaut Goma
    var latitude = <?= $position ? htmlspecialchars($position->latitude) : -1.6585 ?>;
    var longitude = <?= $position ? htmlspecialchars($position->longitude) : 29.2203 ?>;

    var map = L.map('map').setView([latitude, longitude], 12); // Coordonnées par défaut de Goma

    // Ajouter une couche de tuiles OpenStreetMap
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 18,
        attribution: '© OpenStreetMap'
    }).addTo(map);

    // Ajouter un marqueur pour la position actuelle de l'enfant
    if (latitude && longitude) {
        L.marker([latitude, longitude]).addTo(map)
            .bindPopup('Position actuelle : latitude=' + latitude + ', longitude=' + longitude)
            .openPopup();
    }
</script>

</body>
</html>
