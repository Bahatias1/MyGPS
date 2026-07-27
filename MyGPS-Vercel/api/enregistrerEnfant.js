// API Endpoint 100% compatible avec enregistrerEnfant.php
const { getPool, memoryStore } = require('../lib/db');

module.exports = async function handler(req, res) {
  if (req.method !== 'POST') {
    return res.status(405).json({ error: "Méthode non autorisée" });
  }

  const { nom, postNom, prenom, age, classe, photo } = req.body || {};
  const photoName = photo || 'default.jpg';

  const pool = getPool();
  if (pool) {
    try {
      const [result] = await pool.query(
        "INSERT INTO enfant (nom, postNom, prenom, age, classe, photo) VALUES (?, ?, ?, ?, ?, ?)",
        [nom, postNom, prenom, parseInt(age), classe, photoName]
      );
      // Créer une position par défaut à Goma pour le nouvel enfant
      await pool.query(
        "INSERT INTO position (idEnfant, latitude, longitude, etat) VALUES (?, -1.6585, 29.2203, 0)",
        [result.insertId]
      );
      return res.status(200).json({ success: true, message: "Données enregistrées avec succès!" });
    } catch (err) {
      return res.status(500).json({ error: "Erreur : " + err.message });
    }
  } else {
    // Mode Démo mémoire
    const newId = memoryStore.enfants.length + 1;
    memoryStore.enfants.push({
      idEnfant: newId,
      nom, postNom, prenom, age: parseInt(age), classe, photo: photoName
    });
    memoryStore.positions.push({
      id: memoryStore.positions.length + 1,
      idEnfant: newId,
      latitude: -1.6585,
      longitude: 29.2203,
      etat: 0
    });
    return res.status(200).json({ success: true, message: "Données enregistrées avec succès!" });
  }
};
