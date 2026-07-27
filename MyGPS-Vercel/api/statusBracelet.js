// API Endpoint - Vérification du statut de connexion du bracelet GPS
// Vérifie si le bracelet envoie des données récentes à Supabase
// et retourne le diagnostic complet de connexion
const { supabaseQuery, getDbType, memoryStore } = require('../lib/db');

module.exports = async function handler(req, res) {
  res.setHeader('Access-Control-Allow-Credentials', true);
  res.setHeader('Access-Control-Allow-Origin', '*');
  res.setHeader('Access-Control-Allow-Methods', 'GET,OPTIONS');
  res.setHeader('Access-Control-Allow-Headers', 'Content-Type, Accept');

  if (req.method === 'OPTIONS') {
    return res.status(200).end();
  }

  const dbType = getDbType();

  // Seuil de détection : si la dernière position est < 60 secondes => bracelet connecté
  const CONNECTED_THRESHOLD_SECONDS = 60;

  try {
    if (dbType !== 'memory') {
      // Récupérer le dernier élève inscrit
      let dernierEnfant = null;
      try {
        const enfants = await supabaseQuery('enfant', {
          filters: 'order=idenfant.desc&limit=1',
          select: '*'
        });
        dernierEnfant = enfants && enfants.length > 0 ? enfants[0] : null;
      } catch (e) {
        dernierEnfant = null;
      }

      // Récupérer la toute dernière position enregistrée (toutes sections confondues)
      let lastPositions = [];
      try {
        lastPositions = await supabaseQuery('position', {
          filters: 'order=id.desc&limit=5',
          select: '*'
        });
      } catch (e) {
        lastPositions = [];
      }

      const lastPos = lastPositions && lastPositions.length > 0 ? lastPositions[0] : null;

      // Vérification du timestamp (si colonne created_at existe dans Supabase)
      let braceletStatus = 'unknown';
      let lastSeenSeconds = null;
      let lastSeenText = 'Inconnue';

      if (lastPos) {
        if (lastPos.created_at) {
          const posTime = new Date(lastPos.created_at).getTime();
          const nowTime = Date.now();
          lastSeenSeconds = Math.floor((nowTime - posTime) / 1000);

          if (lastSeenSeconds < CONNECTED_THRESHOLD_SECONDS) {
            braceletStatus = 'connected';
            lastSeenText = `Il y a ${lastSeenSeconds}s`;
          } else if (lastSeenSeconds < 3600) {
            braceletStatus = 'idle';
            lastSeenText = `Il y a ${Math.floor(lastSeenSeconds / 60)} min`;
          } else {
            braceletStatus = 'offline';
            lastSeenText = `Il y a ${Math.floor(lastSeenSeconds / 3600)}h`;
          }
        } else {
          // Pas de timestamp => on considère que des données existent (potentiellement actif)
          braceletStatus = 'data_available';
          lastSeenText = 'Données reçues (heure inconnue)';
        }
      } else {
        braceletStatus = 'offline';
        lastSeenText = 'Aucune donnée reçue';
      }

      return res.status(200).json({
        status: braceletStatus,
        dernierEleve: dernierEnfant ? {
          idEnfant: dernierEnfant.idenfant || dernierEnfant.idEnfant,
          nom: dernierEnfant.nom,
          prenom: dernierEnfant.prenom,
          classe: dernierEnfant.classe
        } : null,
        dernierePosition: lastPos ? {
          latitude: parseFloat(lastPos.latitude),
          longitude: parseFloat(lastPos.longitude),
          etat: parseInt(lastPos.etat),
          idEnfant: lastPos.idenfant || lastPos.idEnfant,
          created_at: lastPos.created_at || null
        } : null,
        lastSeenSeconds: lastSeenSeconds,
        lastSeenText: lastSeenText,
        totalPositions: lastPositions.length,
        timestamp: new Date().toISOString()
      });

    } else {
      // Mode Démo Mémoire
      const dernierEnfant = memoryStore.enfants.length > 0
        ? memoryStore.enfants[memoryStore.enfants.length - 1]
        : null;

      const lastPos = memoryStore.positions.length > 0
        ? memoryStore.positions[memoryStore.positions.length - 1]
        : null;

      return res.status(200).json({
        status: 'demo',
        dernierEleve: dernierEnfant ? {
          idEnfant: dernierEnfant.idEnfant || dernierEnfant.idenfant,
          nom: dernierEnfant.nom,
          prenom: dernierEnfant.prenom,
          classe: dernierEnfant.classe
        } : null,
        dernierePosition: lastPos ? {
          latitude: parseFloat(lastPos.latitude),
          longitude: parseFloat(lastPos.longitude),
          etat: parseInt(lastPos.etat),
          idEnfant: lastPos.idenfant || lastPos.idEnfant,
          created_at: null
        } : null,
        lastSeenSeconds: null,
        lastSeenText: 'Mode démo actif',
        totalPositions: memoryStore.positions.length,
        timestamp: new Date().toISOString()
      });
    }
  } catch (err) {
    console.error('StatusBracelet Error:', err);
    return res.status(200).json({
      status: 'error',
      error: err.message,
      dernierEleve: null,
      dernierePosition: null,
      lastSeenSeconds: null,
      lastSeenText: 'Erreur de diagnostic',
      totalPositions: 0,
      timestamp: new Date().toISOString()
    });
  }
};
