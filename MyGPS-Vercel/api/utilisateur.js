// API Endpoint 100% compatible avec utilisateur.php (Inscription parent)
const { getPool, memoryStore } = require('../lib/db');

module.exports = async function handler(req, res) {
  if (req.method !== 'POST') {
    return res.status(405).json({ error: "Méthode non autorisée" });
  }

  const { prenom, nom, email, motDePasse, motDePasseConfirme } = req.body || {};

  if (motDePasse !== motDePasseConfirme) {
    return res.status(400).json({ success: false, message: "Les mots de passe ne correspondent pas." });
  }

  const pool = getPool();
  if (pool) {
    try {
      await pool.query(
        "INSERT INTO utilisateurs (prenom, nom, email, motDePass) VALUES (?, ?, ?, ?)",
        [prenom, nom, email, motDePasse]
      );
      return res.status(200).json({ success: true, message: "Données enregistrées avec succès!" });
    } catch (err) {
      return res.status(500).json({ error: "Erreur : " + err.message });
    }
  } else {
    // Mode Démo mémoire
    memoryStore.utilisateurs.push({
      id: memoryStore.utilisateurs.length + 1,
      prenom, nom, email, motDePass: motDePasse
    });
    return res.status(200).json({ success: true, message: "Données enregistrées avec succès!" });
  }
};
