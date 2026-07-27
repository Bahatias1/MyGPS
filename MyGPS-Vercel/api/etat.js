// API Endpoint 100% compatible avec etat.php
// Utilisé par le bracelet GPS pour envoyer l'état SOS / Alerte
const { getPool, memoryStore } = require('../lib/db');

module.exports = async function handler(req, res) {
  // Configurer les en-têtes CORS pour les requêtes IoT / Bracelet
  res.setHeader('Access-Control-Allow-Credentials', true);
  res.setHeader('Access-Control-Allow-Origin', '*');
  res.setHeader('Access-Control-Allow-Methods', 'GET,OPTIONS,PATCH,DELETE,POST,PUT');
  res.setHeader(
    'Access-Control-Allow-Headers',
    'X-CSRF-Token, X-Requested-With, Accept, Accept-Version, Content-Length, Content-MD5, Content-Type, Date, X-Api-Version'
  );

  if (req.method === 'OPTIONS') {
    res.status(200).end();
    return;
  }

  // Récupération des données POST (idEnfant et etat)
  const body = req.body || {};
  const id = body.idEnfant !== undefined ? body.idEnfant : (req.query.idEnfant || null);
  const state = body.etat !== undefined ? body.etat : (req.query.etat || null);

  if (id === null || state === null) {
    return res.status(200).json({ message: "Invalid input data" });
  }

  const pool = getPool();
  if (pool) {
    try {
      const [result] = await pool.query(
        "UPDATE position SET etat = ? WHERE idEnfant = ?",
        [parseInt(state), parseInt(id)]
      );
      return res.status(200).send("changement reussi");
    } catch (err) {
      console.error("Database error:", err);
      return res.status(500).json({ message: "Error updating record" });
    }
  } else {
    // Mode Démo mémoire
    const enfantId = parseInt(id);
    const newState = parseInt(state);
    let updated = false;
    memoryStore.positions.forEach(p => {
      if (p.idEnfant === enfantId) {
        p.etat = newState;
        updated = true;
      }
    });
    if (updated) {
      return res.status(200).send("changement reussi");
    } else {
      // Si la position n'existe pas encore, la créer
      memoryStore.positions.push({
        id: memoryStore.positions.length + 1,
        idEnfant: enfantId,
        latitude: -1.6585,
        longitude: 29.2203,
        etat: newState
      });
      return res.status(200).send("changement reussi");
    }
  }
};
