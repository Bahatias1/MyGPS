// API Endpoint 100% compatible avec etat.php
const { dbQuery, getDbType, memoryStore } = require('../lib/db');

module.exports = async function handler(req, res) {
  res.setHeader('Access-Control-Allow-Credentials', true);
  res.setHeader('Access-Control-Allow-Origin', '*');
  res.setHeader('Access-Control-Allow-Methods', 'GET,OPTIONS,PATCH,DELETE,POST,PUT');
  res.setHeader('Access-Control-Allow-Headers', 'X-CSRF-Token, X-Requested-With, Accept, Accept-Version, Content-Length, Content-MD5, Content-Type, Date, X-Api-Version');

  if (req.method === 'OPTIONS') {
    return res.status(200).end();
  }

  try {
    const body = req.body || {};
    const id = body.idEnfant !== undefined ? body.idEnfant : (req.query.idEnfant || null);
    const state = body.etat !== undefined ? body.etat : (req.query.etat || null);

    if (id === null || state === null) {
      return res.status(200).json({ message: "Invalid input data" });
    }

    const dbType = getDbType();
    if (dbType !== 'memory') {
      await dbQuery(
        "UPDATE position SET etat = ? WHERE idEnfant = ?",
        [parseInt(state), parseInt(id)]
      );
      return res.status(200).send("changement reussi");
    } else {
      // Mode Démo mémoire
      const enfantId = parseInt(id);
      const newState = parseInt(state);
      let updated = false;
      memoryStore.positions.forEach(p => {
        if ((p.idEnfant || p.idenfant) === enfantId) {
          p.etat = newState;
          updated = true;
        }
      });
      if (!updated) {
        memoryStore.positions.push({
          id: memoryStore.positions.length + 1,
          idenfant: enfantId,
          latitude: -1.6585,
          longitude: 29.2203,
          etat: newState
        });
      }
      return res.status(200).send("changement reussi");
    }
  } catch (err) {
    console.error("Etat API Error:", err);
    return res.status(500).json({ message: "Error updating record" });
  }
};
