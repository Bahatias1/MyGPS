// API Endpoint 100% compatible avec lacaliser.php & Supabase
const { dbQuery, getDbType, memoryStore } = require('../lib/db');

module.exports = async function handler(req, res) {
  const id = parseInt(req.query.id || req.query.idEnfant || 0);
  const dbType = getDbType();

  if (dbType !== 'memory') {
    try {
      const enfants = await dbQuery("SELECT * FROM enfant WHERE idEnfant = ?", [id]);
      const positions = await dbQuery("SELECT * FROM position WHERE idEnfant = ? ORDER BY id ASC", [id]);

      const enfant = enfants && enfants.length > 0 ? enfants[0] : null;
      // Normalize column names to lowercase/camelCase for JS frontend
      const normalizedEnfant = enfant ? {
        idEnfant: enfant.idenfant || enfant.idEnfant,
        nom: enfant.nom,
        postNom: enfant.postnom || enfant.postNom,
        prenom: enfant.prenom,
        age: enfant.age,
        classe: enfant.classe,
        photo: enfant.photo
      } : null;

      const normalizedPositions = (positions || []).map(p => ({
        id: p.id,
        idEnfant: p.idenfant || p.idEnfant,
        latitude: parseFloat(p.latitude),
        longitude: parseFloat(p.longitude),
        etat: parseInt(p.etat)
      }));

      return res.status(200).json({
        enfant: normalizedEnfant,
        positions: normalizedPositions
      });
    } catch (err) {
      console.error("Database error:", err);
      return res.status(500).json({ error: "Erreur de connexion : " + err.message });
    }
  } else {
    // Mode Démo mémoire
    const enfant = memoryStore.enfants.find(e => (e.idEnfant || e.idenfant) === id) || null;
    const positions = memoryStore.positions.filter(p => (p.idEnfant || p.idenfant) === id);

    return res.status(200).json({
      enfant: enfant,
      positions: positions
    });
  }
};
