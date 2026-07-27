// API Endpoint 100% compatible avec enregistrerEnfant.php
const { supabaseQuery, getDbType, memoryStore } = require('../lib/db');

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
      // Étape 1 : Insérer l'enfant et récupérer son ID généré
      const inserted = await supabaseQuery('enfant', {
        method: 'POST',
        body: {
          nom,
          postnom: postNom || postNom,
          prenom,
          age: parseInt(age),
          classe,
          photo: photoName
        },
        returning: 'representation'
      });

      // Étape 2 : Récupérer l'ID du nouvel enfant
      const newEnfant = Array.isArray(inserted) ? inserted[0] : inserted;
      const newId = newEnfant ? (newEnfant.idenfant || newEnfant.idEnfant) : null;

      if (newId) {
        // Étape 3 : Créer une position par défaut à Goma avec l'ID correct
        await supabaseQuery('position', {
          method: 'POST',
          body: {
            idenfant: newId,
            latitude: -1.6585,
            longitude: 29.2203,
            etat: 0
          }
        });
      }

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
