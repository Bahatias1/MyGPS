// API Endpoint 100% compatible avec lacaliser.php
// Utilisé pour charger les détails de l'enfant et l'historique des positions GPS pour la carte Leaflet
const { getPool, memoryStore } = require('../lib/db');

module.exports = async function handler(req, res) {
  const id = parseInt(req.query.id || req.query.idEnfant || 0);

  const pool = getPool();
  if (pool) {
    try {
      const [enfants] = await pool.query("SELECT * FROM enfant WHERE idEnfant = ?", [id]);
      const [positions] = await pool.query("SELECT * FROM position WHERE idEnfant = ? ORDER BY id ASC", [id]);

      return res.status(200).json({
        enfant: enfants[0] || null,
        positions: positions || []
      });
    } catch (err) {
      console.error("Database error:", err);
      return res.status(500).json({ error: "Erreur de connexion : " + err.message });
    }
  } else {
    // Mode Démo mémoire
    const enfant = memoryStore.enfants.find(e => e.idEnfant === id) || null;
    const positions = memoryStore.positions.filter(p => p.idEnfant === id);

    return res.status(200).json({
      enfant: enfant,
      positions: positions
    });
  }
};
