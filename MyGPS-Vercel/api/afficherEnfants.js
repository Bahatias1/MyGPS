// API Endpoint 100% compatible avec afficherEnfants.php
const { getPool, memoryStore } = require('../lib/db');

module.exports = async function handler(req, res) {
  const pool = getPool();
  if (pool) {
    try {
      const [enfants] = await pool.query("SELECT * FROM enfant");
      return res.status(200).json(enfants);
    } catch (err) {
      return res.status(500).json({ error: "Erreur de connexion : " + err.message });
    }
  } else {
    // Mode Démo mémoire
    return res.status(200).json(memoryStore.enfants);
  }
};
