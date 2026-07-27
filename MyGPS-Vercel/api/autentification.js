// API Endpoint 100% compatible avec autentification.php
const { dbQuery, getDbType, memoryStore } = require('../lib/db');

module.exports = async function handler(req, res) {
  res.setHeader('Content-Type', 'application/json');

  if (req.method !== 'POST') {
    return res.status(405).json({ success: false, message: "Méthode non autorisée" });
  }

  try {
    const body = req.body || {};
    const email = body.email || '';
    const motDePasse = body.motDePasse || body.motDePass || '';

    const dbType = getDbType();

    if (dbType !== 'memory') {
      const users = await dbQuery(
        "SELECT id, prenom, nom, motDePass, motdepass FROM utilisateurs WHERE email = ?",
        [email]
      );
      const user = users && users.length > 0 ? users[0] : null;
      const storedPass = user ? (user.motDePass || user.motdepass) : null;

      if (user && storedPass === motDePasse) {
        return res.status(200).json({ success: true, message: "Mot de passe correct", userId: user.id });
      } else {
        return res.status(400).json({ success: false, message: "Mot de passe ou email incorrect" });
      }
    } else {
      // Mode Démo mémoire (admin@sysloc.com / 123456)
      const user = memoryStore.utilisateurs.find(u => u.email.toLowerCase() === email.toLowerCase());
      const storedPass = user ? (user.motDePass || user.motdepass) : null;

      if (user && storedPass === motDePasse) {
        return res.status(200).json({ success: true, message: "Mot de passe correct", userId: user.id });
      } else {
        return res.status(400).json({ success: false, message: "Mot de passe ou email incorrect" });
      }
    }
  } catch (err) {
    console.error("Auth API Error:", err);
    return res.status(500).json({ success: false, message: "Erreur serveur : " + err.message });
  }
};
