// API Authentification - Compatible Supabase + fallback mémoire
const { supabaseQuery, memoryStore } = require('../lib/db');

module.exports = async function handler(req, res) {
  res.setHeader('Content-Type', 'application/json');

  if (req.method !== 'POST') {
    return res.status(405).json({ success: false, message: "Méthode non autorisée" });
  }

  try {
    const body = req.body || {};
    const email = (body.email || '').trim().toLowerCase();
    // Le formulaire envoie le champ 'motdepass' (tout en minuscules)
    const motDePasse = body.motdepass || body.motDePasse || body.motDePass || '';

    if (!email || !motDePasse) {
      return res.status(400).json({ success: false, message: "Email et mot de passe requis." });
    }

    // -------------------------------------------------------
    // 1. Vérifier le compte administrateur intégré (toujours disponible)
    // -------------------------------------------------------
    const adminAccounts = [
      { id: 0, email: 'admin@sysloc.com', motdepass: '123456', prenom: 'Admin', nom: 'SYSLOC' }
    ];
    const builtinAdmin = adminAccounts.find(
      a => a.email === email && a.motdepass === motDePasse
    );
    if (builtinAdmin) {
      return res.status(200).json({
        success: true,
        message: "Connexion réussie",
        userId: builtinAdmin.id,
        prenom: builtinAdmin.prenom
      });
    }

    // -------------------------------------------------------
    // 2. Chercher dans Supabase (table utilisateurs)
    // -------------------------------------------------------
    try {
      const users = await supabaseQuery('utilisateurs', {
        filters: `email=eq.${encodeURIComponent(email)}`
      });

      if (users && users.length > 0) {
        const user = users[0];
        // Supabase/PostgreSQL stocke en minuscules : motdepass
        const storedPass = user.motdepass || user.motDePass || user.motdePass || '';

        if (storedPass === motDePasse) {
          return res.status(200).json({
            success: true,
            message: "Connexion réussie",
            userId: user.id,
            prenom: user.prenom
          });
        } else {
          return res.status(400).json({ success: false, message: "Mot de passe ou email incorrect" });
        }
      }
    } catch (supaErr) {
      console.warn("Supabase auth lookup failed, using memory fallback:", supaErr.message);
    }

    // -------------------------------------------------------
    // 3. Fallback sur le store mémoire (si Supabase inaccessible)
    // -------------------------------------------------------
    const memUser = memoryStore.utilisateurs.find(
      u => u.email.toLowerCase() === email
    );
    const memPass = memUser ? (memUser.motdepass || memUser.motDePass || '') : '';

    if (memUser && memPass === motDePasse) {
      return res.status(200).json({
        success: true,
        message: "Connexion réussie (mode démo)",
        userId: memUser.id,
        prenom: memUser.prenom
      });
    }

    return res.status(400).json({ success: false, message: "Email ou mot de passe incorrect" });

  } catch (err) {
    console.error("Auth API Error:", err);
    return res.status(500).json({ success: false, message: "Erreur serveur : " + err.message });
  }
};
