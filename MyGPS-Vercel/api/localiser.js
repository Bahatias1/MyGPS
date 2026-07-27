// API Endpoint 100% compatible avec lacaliser.php & Supabase
const { dbQuery, getDbType, memoryStore } = require('../lib/db');

module.exports = async function handler(req, res) {
  let id = parseInt(req.query.id || req.query.idEnfant || 0);
  const dbType = getDbType();

  if (dbType !== 'memory') {
    try {
      let enfant = null;
      if (id > 0) {
        const enfants = await dbQuery("SELECT * FROM enfant WHERE idEnfant = ?", [id]);
        enfant = enfants && enfants.length > 0 ? enfants[0] : null;
      } else {
        // Sélection automatique du dernier élève inscrit si aucun ID n'est transmis
        const derniersEnfants = await dbQuery("SELECT * FROM enfant ORDER BY idEnfant DESC LIMIT 1");
        enfant = derniersEnfants && derniersEnfants.length > 0 ? derniersEnfants[0] : null;
      }

      const targetId = enfant ? (enfant.idenfant || enfant.idEnfant) : id;
      const positions = targetId ? await dbQuery("SELECT * FROM position WHERE idEnfant = ? ORDER BY id ASC", [targetId]) : [];

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
    // Mode Démo mémoire : Récupérer l'élève demandé ou le tout dernier inscrit
    let enfant = null;
    if (id > 0) {
      enfant = memoryStore.enfants.find(e => (e.idEnfant || e.idenfant) === id) || null;
    } else if (memoryStore.enfants.length > 0) {
      enfant = memoryStore.enfants[memoryStore.enfants.length - 1];
    }

    const targetId = enfant ? (enfant.idEnfant || enfant.idenfant) : id;
    const positions = memoryStore.positions.filter(p => (p.idEnfant || p.idenfant) === targetId);

    return res.status(200).json({
      enfant: enfant,
      positions: positions
    });
  }
};

