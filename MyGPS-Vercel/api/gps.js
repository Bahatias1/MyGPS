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

  // Extraction des données du bracelet ESP8266 (sendval1 = idEnfant, sendval2 = latitude, sendval3 = longitude, sendval4 = etat)
  const idEnfant = source.sendval1 !== undefined ? parseInt(source.sendval1) : (source.idEnfant !== undefined ? parseInt(source.idEnfant) : (source.id ? parseInt(source.id) : null));
  const latitude = source.sendval2 !== undefined ? parseFloat(source.sendval2) : (source.latitude !== undefined ? parseFloat(source.latitude) : (source.lat ? parseFloat(source.lat) : null));
  const longitude = source.sendval3 !== undefined ? parseFloat(source.sendval3) : (source.longitude !== undefined ? parseFloat(source.longitude) : (source.lng ? parseFloat(source.lng) : null));
  const etat = source.sendval4 !== undefined ? parseInt(source.sendval4) : (source.etat !== undefined ? parseInt(source.etat) : 0);

  if (!idEnfant || isNaN(latitude) || isNaN(longitude)) {
    return res.status(400).json({
      status: "error",
      message: "Paramètres manquants du bracelet. Requis : sendval1 (idEnfant), sendval2 (latitude), sendval3 (longitude)"
    });
  }

  const dbType = getDbType();
  if (dbType !== 'memory') {
    try {
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
    // Mode Démo Mémoire
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
