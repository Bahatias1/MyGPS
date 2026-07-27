// API Endpoint 100% compatible avec enregistrerEnfant.php
const { dbQuery, getDbType, memoryStore } = require('../lib/db');

module.exports = async function handler(req, res) {
  res.setHeader('Content-Type', 'application/json');

  if (req.method !== 'POST') {
    return res.status(405).json({ success: false, message: "Méthode non autorisée" });
  }

  try {
    const { nom, postNom, prenom, age, classe, photo } = req.body || {};
    const photoName = photo || 'default.jpg';
    const dbType = getDbType();

    if (dbType !== 'memory') {
      const result = await dbQuery(
        "INSERT INTO enfant (nom, postNom, prenom, age, classe, photo) VALUES (?, ?, ?, ?, ?, ?)",
        [nom, postNom, prenom, parseInt(age), classe, photoName]
      );
      // Créer une position par défaut à Goma pour le nouvel enfant
      await dbQuery(
        "INSERT INTO position (idEnfant, latitude, longitude, etat) VALUES (1, -1.6585, 29.2203, 0)"
      );
      return res.status(200).json({ success: true, message: "Données enregistrées avec succès!" });
    } else {
      // Mode Démo mémoire
      const newId = memoryStore.enfants.length + 1;
      memoryStore.enfants.push({
        idEnfant: newId,
        nom, postNom, prenom, age: parseInt(age), classe, photo: photoName
      });
      memoryStore.positions.push({
        id: memoryStore.positions.length + 1,
        idenfant: newId,
        latitude: -1.6585,
        longitude: 29.2203,
        etat: 0
      });
      return res.status(200).json({ success: true, message: "Données enregistrées avec succès!" });
    }
  } catch (err) {
    console.error("EnregistrerEnfant API Error:", err);
    return res.status(500).json({ success: false, message: "Erreur : " + err.message });
  }
};
