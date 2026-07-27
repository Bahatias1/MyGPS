// API Endpoint 100% compatible avec supprimer.php
const { getPool, memoryStore } = require('../lib/db');

module.exports = async function handler(req, res) {
  if (req.method !== 'POST') {
    return res.status(405).json({ error: "Méthode non autorisée" });
  }

  const { idEnfant } = req.body || {};
  const id = parseInt(idEnfant);

  const pool = getPool();
  if (pool) {
    try {
      await pool.query("DELETE FROM position WHERE idEnfant = ?", [id]);
      await pool.query("DELETE FROM enfant WHERE idEnfant = ?", [id]);
      return res.status(200).json({ success: true, message: "Enfant supprimé avec succès." });
    } catch (err) {
      return res.status(500).json({ error: "Erreur : " + err.message });
    }
  } else {
    // Mode Démo mémoire
    memoryStore.enfants = memoryStore.enfants.filter(e => e.idEnfant !== id);
    memoryStore.positions = memoryStore.positions.filter(p => p.idEnfant !== id);
    return res.status(200).json({ success: true, message: "Enfant supprimé avec succès." });
  }
};
