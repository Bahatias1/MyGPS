// API Endpoint 100% compatible avec autentification.php
const { getPool, memoryStore } = require('../lib/db');

module.exports = async function handler(req, res) {
  if (req.method !== 'POST') {
    return res.status(405).json({ error: "Méthode non autorisée" });
  }

  const { email, motDePasse } = req.body || {};

  const pool = getPool();
  if (pool) {
    try {
      const [users] = await pool.query(
        "SELECT id, prenom, nom, motDePass FROM utilisateurs WHERE email = ?",
        [email]
      );
      const user = users[0];

      if (user && user.motDePass === motDePasse) {
        return res.status(200).json({ success: true, message: "Mot de passe correct", userId: user.id });
      } else {
        return res.status(400).json({ success: false, message: "Mot de passe ou email incorrect" });
      }
    } catch (err) {
      return res.status(500).json({ error: "Erreur de connexion : " + err.message });
    }
  } else {
    // Mode Démo mémoire
    const user = memoryStore.utilisateurs.find(u => u.email === email);
    if (user && user.motDePass === motDePasse) {
      return res.status(200).json({ success: true, message: "Mot de passe correct", userId: user.id });
    } else {
      return res.status(400).json({ success: false, message: "Mot de passe ou email incorrect" });
    }
  }
};
