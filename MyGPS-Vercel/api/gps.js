// API Endpoint dédiée à la réception des coordonnées GPS transmises par le bracelet connecté
// Compatible avec les requêtes HTTP GET et POST du module SIM800L / ESP32 / Arduino / Android
const { dbQuery, getDbType, memoryStore } = require('../lib/db');

module.exports = async function handler(req, res) {
  // CORS support
  res.setHeader('Access-Control-Allow-Credentials', true);
  res.setHeader('Access-Control-Allow-Origin', '*');
  res.setHeader('Access-Control-Allow-Methods', 'GET,POST,OPTIONS');
  res.setHeader('Access-Control-Allow-Headers', 'Content-Type, Accept');

  if (req.method === 'OPTIONS') {
    return res.status(200).end();
  }

  // Lecture des paramètres depuis GET (Query string) ou POST (Body / JSON)
  const source = req.method === 'POST' ? (req.body || {}) : req.query;

  const idEnfant = source.idEnfant !== undefined ? parseInt(source.idEnfant) : (source.id ? parseInt(source.id) : null);
  const latitude = source.latitude !== undefined ? parseFloat(source.latitude) : (source.lat ? parseFloat(source.lat) : null);
  const longitude = source.longitude !== undefined ? parseFloat(source.longitude) : (source.lng ? parseFloat(source.lng) : null);
  const etat = source.etat !== undefined ? parseInt(source.etat) : 0;

  if (!idEnfant || latitude === null || longitude === null) {
    return res.status(400).json({
      status: "error",
      message: "Paramètres manquants. Requis : idEnfant (ou id), latitude (ou lat), longitude (ou lng)"
    });
  }

  const dbType = getDbType();
  if (dbType !== 'memory') {
    try {
      await dbQuery(
        "INSERT INTO position (idEnfant, latitude, longitude, etat) VALUES (?, ?, ?, ?)",
        [idEnfant, latitude, longitude, etat]
      );
      return res.status(200).json({
        status: "success",
        message: "Coordonnées du bracelet enregistrées avec succès !",
        data: { idEnfant, latitude, longitude, etat }
      });
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
    return res.status(200).json({
      status: "success",
      message: "Coordonnées du bracelet enregistrées en mémoire (Mode Démo) !",
      data: { idEnfant, latitude, longitude, etat }
    });
  }
};
