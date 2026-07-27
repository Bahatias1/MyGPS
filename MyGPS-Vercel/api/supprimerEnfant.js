// API Endpoint 100% compatible avec supprimer.php
const { dbQuery, getDbType, memoryStore } = require('../lib/db');

module.exports = async function handler(req, res) {
  res.setHeader('Content-Type', 'application/json');

  if (req.method !== 'POST') {
    return res.status(405).json({ success: false, message: "Méthode non autorisée" });
  }

  try {
    const { idEnfant } = req.body || {};
    const id = parseInt(idEnfant);
    const dbType = getDbType();

    if (dbType !== 'memory') {
      await dbQuery("DELETE FROM position WHERE idEnfant = ?", [id]);
      await dbQuery("DELETE FROM enfant WHERE idEnfant = ?", [id]);
      return res.status(200).json({ success: true, message: "Enfant supprimé avec succès." });
    } else {
      // Mode Démo mémoire
      memoryStore.enfants = memoryStore.enfants.filter(e => (e.idEnfant || e.idenfant) !== id);
      memoryStore.positions = memoryStore.positions.filter(p => (p.idEnfant || p.idenfant) !== id);
      return res.status(200).json({ success: true, message: "Enfant supprimé avec succès." });
    }
  } catch (err) {
    console.error("SupprimerEnfant API Error:", err);
    return res.status(500).json({ success: false, message: "Erreur : " + err.message });
  }
};
