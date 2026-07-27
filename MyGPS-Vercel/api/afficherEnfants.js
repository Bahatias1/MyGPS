// API Endpoint 100% compatible avec afficherEnfants.php
const { dbQuery, getDbType, memoryStore } = require('../lib/db');

module.exports = async function handler(req, res) {
  res.setHeader('Content-Type', 'application/json');

  try {
    const dbType = getDbType();
    if (dbType !== 'memory') {
      const enfants = await dbQuery("SELECT * FROM enfant");
      const normalized = (enfants || []).map(e => ({
        idEnfant: e.idenfant || e.idEnfant,
        nom: e.nom,
        postNom: e.postnom || e.postNom,
        prenom: e.prenom,
        age: e.age,
        classe: e.classe,
        photo: e.photo || 'default.jpg'
      }));
      return res.status(200).json(normalized);
    } else {
      // Mode Démo mémoire
      const normalized = memoryStore.enfants.map(e => ({
        idEnfant: e.idEnfant || e.idenfant,
        nom: e.nom,
        postNom: e.postNom || e.postnom,
        prenom: e.prenom,
        age: e.age,
        classe: e.classe,
        photo: e.photo || 'default.jpg'
      }));
      return res.status(200).json(normalized);
    }
  } catch (err) {
    console.error("AfficherEnfants API Error:", err);
    return res.status(500).json({ error: "Erreur : " + err.message });
  }
};
