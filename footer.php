<div class="py-6 px-6 text-center">
          <p class="mb-0 fs-4">Design and Developed by <a href="https://linktr.ee/bahatias1" target="_blank" class="pe-1 text-primary text-decoration-underline">Patrick Selebunga</a></p>
        </div>
      </div>
    </div>
  </div>
  <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
  <script>
      // Étape 1 : Initialiser la carte centrée sur Goma, RDC
      var map = L.map('map').setView([-1.6585, 29.2203], 12); // Coordonnées de Goma

      // Étape 2 : Ajouter une couche de tuiles (OpenStreetMap)
      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
          maxZoom: 500,
          attribution: '© OpenStreetMap'
      }).addTo(map);

      
      // Étape 3 : Ajouter un marqueur à Goma
      L.marker(-2.65845, -1.6585).addTo(map)
          .bindPopup(latitude=-1.6585, longitude=-1.6585)
          .openPopup();
          
  </script>

  <script src="../assets/libs/jquery/dist/jquery.min.js"></script>
  <script src="../assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
  <script src="../assets/js/sidebarmenu.js"></script>
  <script src="../assets/js/app.min.js"></script>
  <script src="../assets/libs/apexcharts/dist/apexcharts.min.js"></script>
  <script src="../assets/libs/simplebar/dist/simplebar.js"></script>
  <script src="../assets/js/dashboard.js"></script>
</body>

</html>