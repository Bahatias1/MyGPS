// API Endpoint 100% compatible avec utilisateur.php (Inscription parent)
const { dbQuery, getDbType, memoryStore } = require('../lib/db');

module.exports = async function handler(req, res) {
  res.setHeader('Content-Type', 'application/json');

  if (req.method !== 'POST') {
    return res.status(405).json({ success: false, message: "Méthode non autorisée" });
  }

  try {
    const { prenom, nom, email, motDePasse, motDePasseConfirme } = req.body || {};

    if (motDePasse !== motDePasseConfirme) {
      return res.status(400).json({ success: false, message: "Les mots de passe ne correspondent pas." });
    }

    const dbType = getDbType();
    if (dbType !== 'memory') {
      await dbQuery(
        "INSERT INTO utilisateurs (prenom, nom, email, motDePass) VALUES (?, ?, ?, ?)",
        [prenom, nom, email, motDePasse]
      );
      return res.status(200).json({ success: true, message: "Données enregistrées avec succès!" });
    } else {
      // Mode Démo mémoire
      memoryStore.utilisateurs.push({
        id: memoryStore.utilisateurs.length + 1,
        prenom, nom, email, motDePass: motDePasse
      });
      return res.status(200).json({ success: true, message: "Données enregistrées avec succès!" });
    }
  } catch (err) {
    console.error("Signup API Error:", err);
    return res.status(500).json({ success: false, message: "Erreur serveur : " + err.message });
  }
};
