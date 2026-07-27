// Helper de gestion de base de données (MySQL / Demo In-Memory)
const mysql = require('mysql2/promise');

// In-Memory store for demonstration when MySQL env vars are not set
const memoryStore = {
  utilisateurs: [
    { id: 1, prenom: "Admin", nom: "SYSLOC", email: "admin@sysloc.com", motDePass: "123456" }
  ],
  enfants: [
    { idEnfant: 1, nom: "Kambale", postNom: "Kasereka", prenom: "Joseph", age: 7, classe: "première maternel", photo: "default.jpg" },
    { idEnfant: 2, nom: "Masika", postNom: "Kavira", prenom: "Grace", age: 6, classe: "deuxième maternel", photo: "default.jpg" }
  ],
  positions: [
    { id: 1, idEnfant: 1, latitude: -1.6585, longitude: 29.2203, etat: 0 },
    { id: 2, idEnfant: 1, latitude: -1.6600, longitude: 29.2250, etat: 0 },
    { id: 3, idEnfant: 2, latitude: -1.6550, longitude: 29.2180, etat: 1 }
  ]
};

let pool = null;

function getPool() {
  if (!pool && process.env.DB_HOST) {
    pool = mysql.createPool({
      host: process.env.DB_HOST,
      user: process.env.DB_USER || 'root',
      password: process.env.DB_PASSWORD || '',
      database: process.env.DB_NAME || 'localisation',
      port: process.env.DB_PORT ? parseInt(process.env.DB_PORT) : 3306,
      waitForConnections: true,
      connectionLimit: 10,
      queueLimit: 0
    });
  }
  return pool;
}

module.exports = {
  getPool,
  memoryStore
};
