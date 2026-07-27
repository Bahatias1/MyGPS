// Helper de gestion de base de données (PostgreSQL/Supabase + MySQL + Demo In-Memory)

const memoryStore = {
  utilisateurs: [
    { id: 1, prenom: "Admin", nom: "SYSLOC", email: "admin@sysloc.com", motdepass: "123456", motDePass: "123456" }
  ],
  enfants: [
    { idenfant: 1, idEnfant: 1, nom: "Kambale", postnom: "Kasereka", postNom: "Kasereka", prenom: "Joseph", age: 7, classe: "première maternel", photo: "default.jpg" },
    { idenfant: 2, idEnfant: 2, nom: "Masika", postnom: "Kavira", postNom: "Kavira", prenom: "Grace", age: 6, classe: "deuxième maternel", photo: "default.jpg" }
  ],
  positions: [
    { id: 1, idenfant: 1, idEnfant: 1, latitude: -1.6585, longitude: 29.2203, etat: 0 },
    { id: 2, idenfant: 1, idEnfant: 1, latitude: -1.6600, longitude: 29.2250, etat: 0 },
    { id: 3, idenfant: 2, idEnfant: 2, latitude: -1.6550, longitude: 29.2180, etat: 1 }
  ]
};

let mysqlPool = null;
let pgPool = null;

function getDbType() {
  if (process.env.DATABASE_URL || process.env.POSTGRES_URL || process.env.SUPABASE_URL) return 'postgres';
  if (process.env.DB_HOST) return 'mysql';
  return 'memory';
}

function getPgPool() {
  if (!pgPool) {
    const { Pool: PgPool } = require('pg');
    const connectionString = process.env.DATABASE_URL || process.env.POSTGRES_URL || process.env.SUPABASE_URL;
    pgPool = new PgPool({
      connectionString,
      ssl: { rejectUnauthorized: false }
    });
  }
  return pgPool;
}

function getMysqlPool() {
  if (!mysqlPool) {
    const mysql = require('mysql2/promise');
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
  return mysqlPool;
}

// Unified query runner for Postgres, MySQL, and Memory
async function dbQuery(sql, params = []) {
  const type = getDbType();
  if (type === 'postgres') {
    const pool = getPgPool();
    let paramIndex = 1;
    const pgSql = sql.replace(/\?/g, () => `$${paramIndex++}`);
    const res = await pool.query(pgSql, params);
    return res.rows;
  } else if (type === 'mysql') {
    const pool = getMysqlPool();
    const [rows] = await pool.query(sql, params);
    return rows;
  }
  return null;
}

module.exports = {
  dbQuery,
  getDbType,
  memoryStore
};
