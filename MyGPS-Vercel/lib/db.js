// Helper de gestion de base de données (PostgreSQL/Supabase + MySQL + Demo In-Memory)
const mysql = require('mysql2/promise');
const { Pool: PgPool } = require('pg');

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

let mysqlPool = null;
let pgPool = null;

function getDbType() {
  if (process.env.DATABASE_URL || process.env.POSTGRES_URL || process.env.SUPABASE_URL) return 'postgres';
  if (process.env.DB_HOST) return 'mysql';
  return 'memory';
}

function getPool() {
  const type = getDbType();
  if (type === 'postgres') {
    if (!pgPool) {
      const connectionString = process.env.DATABASE_URL || process.env.POSTGRES_URL;
      pgPool = new PgPool({
        connectionString,
        ssl: { rejectUnauthorized: false }
      });
    }
    return { type: 'postgres', pool: pgPool };
  } else if (type === 'mysql') {
    if (!mysqlPool) {
      mysqlPool = mysql.createPool({
        host: process.env.DB_HOST,
        user: process.env.DB_USER || 'root',
        password: process.env.DB_PASSWORD || '',
        database: process.env.DB_NAME || 'localisation',
        port: process.env.DB_PORT ? parseInt(process.env.DB_PORT) : 3306,
        waitForConnections: true,
        connectionLimit: 10
      });
    }
    return { type: 'mysql', pool: mysqlPool };
  }
  return { type: 'memory', pool: null };
}

module.exports = {
  getPool,
  getDbType,
  memoryStore
};
