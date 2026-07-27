// API Endpoint dédiée à la réception des coordonnées GPS transmises par le bracelet ESP8266 (bracelet_gps.ino)
// Compatible à 100% avec les paramètres de l'Arduino : sendval1, sendval2, sendval3, sendval4
const { dbQuery, getDbType, supabaseQuery, memoryStore } = require('../lib/db');

module.exports = async function handler(req, res) {
  // CORS support pour les requêtes HTTP envoyées par l'ESP8266
  res.setHeader('Access-Control-Allow-Credentials', true);
  res.setHeader('Access-Control-Allow-Origin', '*');
  res.setHeader('Access-Control-Allow-Methods', 'GET,POST,OPTIONS');
  res.setHeader('Access-Control-Allow-Headers', 'Content-Type, Accept');

  if (req.method === 'OPTIONS') {
    return res.status(200).end();
  }

  // Lecture des paramètres depuis GET (Query string) ou POST (Body / Form-urlencoded)
  const source = req.method === 'POST' ? (req.body || {}) : req.query;

  // Extraction des données du bracelet ESP8266
  let idEnfant = source.sendval1 !== undefined ? parseInt(source.sendval1) : (source.idEnfant !== undefined ? parseInt(source.idEnfant) : (source.id ? parseInt(source.id) : null));
  const latitude = source.sendval2 !== undefined ? parseFloat(source.sendval2) : (source.latitude !== undefined ? parseFloat(source.latitude) : (source.lat ? parseFloat(source.lat) : null));
  const longitude = source.sendval3 !== undefined ? parseFloat(source.sendval3) : (source.longitude !== undefined ? parseFloat(source.longitude) : (source.lng ? parseFloat(source.lng) : null));
  const etat = source.sendval4 !== undefined ? parseInt(source.sendval4) : (source.etat !== undefined ? parseInt(source.etat) : 0);

  if (isNaN(latitude) || isNaN(longitude)) {
    return res.status(400).json({
      status: "error",
      message: "Paramètres manquants du bracelet. Requis : latitude et longitude validés."
    });
  }

  const dbType = getDbType();
  if (dbType !== 'memory') {
    try {
      // Si aucun idEnfant n'est passé par le bracelet, affecter au dernier élève inscrit
      if (!idEnfant || isNaN(idEnfant)) {
        const dernierEnfant = await dbQuery("SELECT * FROM enfant ORDER BY idEnfant DESC LIMIT 1");
        if (dernierEnfant && dernierEnfant.length > 0) {
          idEnfant = dernierEnfant[0].idenfant || dernierEnfant[0].idEnfant;
        }
      }

      if (!idEnfant) {
        return res.status(400).json({ status: "error", message: "Aucun élève trouvé dans le système pour recevoir la position." });
      }

      await supabaseQuery('position', {
        method: 'POST',
        body: {
          idenfant: idEnfant,
          latitude: latitude,
          longitude: longitude,
          etat: etat
        }
      });
      return res.status(200).send("changement reussi");
    } catch (err) {
      console.error("Database Error:", err);
      return res.status(500).json({ status: "error", message: err.message });
    }
  } else {
    // Mode Démo Mémoire : Affectation au dernier élève inscrit par défaut
    if (!idEnfant || isNaN(idEnfant)) {
      if (memoryStore.enfants.length > 0) {
        const lastEnfant = memoryStore.enfants[memoryStore.enfants.length - 1];
        idEnfant = lastEnfant.idEnfant || lastEnfant.idenfant;
      }
    }

    if (!idEnfant) idEnfant = 1;

    memoryStore.positions.push({
      id: memoryStore.positions.length + 1,
      idenfant: idEnfant,
      latitude: latitude,
      longitude: longitude,
      etat: etat
    });
    return res.status(200).send("changement reussi");
  }
};

