// Connexion à Supabase via l'API REST (PostgREST) - ZÉRO configuration de password requise
// Utilise la service_role key pour un accès complet à la base de données

const SUPABASE_URL = process.env.SUPABASE_PROJECT_URL || "https://qrsukiatatwsyitcuuql.supabase.co";
const SUPABASE_SERVICE_KEY = process.env.SUPABASE_SERVICE_KEY || "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InFyc3VraWF0YXR3c3lpdGN1dXFsIiwicm9sZSI6InNlcnZpY2Vfcm9sZSIsImlhdCI6MTc4NTEyNjE1MCwiZXhwIjoyMTAwNzAyMTUwfQ.BQe2omWFo6mcGEZ71CGvyuu7FzfP3cpvInSNgbl3z5w";

const memoryStore = {
  utilisateurs: [
    { id: 1, prenom: "Admin", nom: "SYSLOC", email: "admin@sysloc.com", motdepass: "123456" }
  ],
  enfants: [
    { idenfant: 1, nom: "Kambale", postnom: "Kasereka", prenom: "Joseph", age: 7, classe: "première maternel", photo: "default.jpg" },
    { idenfant: 2, nom: "Masika", postnom: "Kavira", prenom: "Grace", age: 6, classe: "deuxième maternel", photo: "default.jpg" }
  ],
  positions: [
    { id: 1, idenfant: 1, latitude: -1.6585, longitude: 29.2203, etat: 0 },
    { id: 2, idenfant: 1, latitude: -1.6600, longitude: 29.2250, etat: 0 },
    { id: 3, idenfant: 2, latitude: -1.6550, longitude: 29.2180, etat: 1 }
  ]
};

// Client REST Supabase - pas besoin de pg ou mysql2, juste fetch !
async function supabaseQuery(table, options = {}) {
  const { method = 'GET', filters = '', body = null, select = '*', returning = 'representation' } = options;

  let url = `${SUPABASE_URL}/rest/v1/${table}?select=${select}`;
  if (filters) url += `&${filters}`;

  const headers = {
    "apikey": SUPABASE_SERVICE_KEY,
    "Authorization": `Bearer ${SUPABASE_SERVICE_KEY}`,
    "Content-Type": "application/json",
    "Prefer": (method === 'POST' || method === 'PATCH') ? "return=representation" : ''
  };

  const fetchOptions = { method, headers };
  if (body && method !== 'GET') {
    fetchOptions.body = JSON.stringify(body);
  }

  const res = await fetch(url, fetchOptions);

  if (!res.ok) {
    const errText = await res.text();
    throw new Error(`Supabase REST error (${res.status}): ${errText}`);
  }

  if (res.status === 204) return [];
  return await res.json();
}

// Unified SQL-like interface for backward compatibility
async function dbQuery(sql, params = []) {
  // Check if we should use Supabase REST (auto-detected from service key presence)
  if (SUPABASE_SERVICE_KEY && !process.env.DATABASE_URL && !process.env.DB_HOST) {
    // Parse simple SQL patterns into REST API calls
    return await executeViaRest(sql, params);
  }

  // Fallback to PostgreSQL direct if DATABASE_URL is set
  if (process.env.DATABASE_URL || process.env.POSTGRES_URL) {
    const { Pool } = require('pg');
    if (!global._pgPool) {
      global._pgPool = new Pool({
        connectionString: process.env.DATABASE_URL || process.env.POSTGRES_URL,
        ssl: { rejectUnauthorized: false }
      });
    }
    let idx = 1;
    const pgSql = sql.replace(/\?/g, () => `$${idx++}`);
    const result = await global._pgPool.query(pgSql, params);
    return result.rows;
  }

  return null; // signals memory mode
}

async function executeViaRest(sql, params) {
  const sqlUpper = sql.trim().toUpperCase();

  // SELECT queries
  if (sqlUpper.startsWith('SELECT')) {
    const tableMatch = sql.match(/FROM\s+(\w+)/i);
    if (!tableMatch) return [];
    const table = tableMatch[1];

    let filters = '';
    if (params.length > 0) {
      const whereMatch = sql.match(/WHERE\s+(.+?)(?:\s+ORDER|$)/is);
      if (whereMatch) {
        const condition = whereMatch[1].trim();
        const colMatch = condition.match(/(\w+)\s*=\s*\?/);
        if (colMatch && params[0] !== undefined) {
          const col = colMatch[1].toLowerCase();
          filters = `${col}=eq.${encodeURIComponent(params[0])}`;
        }
      }
      // ORDER BY
      const orderMatch = sql.match(/ORDER BY\s+(\w+)\s*(ASC|DESC)?/i);
      if (orderMatch) {
        const dir = (orderMatch[2] || 'ASC').toLowerCase();
        filters += `&order=${orderMatch[1]}.${dir}`;
      }
    }
    return await supabaseQuery(table, { filters });
  }

  // INSERT queries
  if (sqlUpper.startsWith('INSERT INTO')) {
    const tableMatch = sql.match(/INSERT INTO\s+(\w+)\s*\(([^)]+)\)/i);
    if (!tableMatch) return [];
    const table = tableMatch[1];
    const cols = tableMatch[2].split(',').map(c => c.trim().toLowerCase());
    const body = {};
    cols.forEach((col, i) => { body[col] = params[i]; });
    return await supabaseQuery(table, { method: 'POST', body });
  }

  // UPDATE queries
  if (sqlUpper.startsWith('UPDATE')) {
    const tableMatch = sql.match(/UPDATE\s+(\w+)\s+SET/i);
    if (!tableMatch) return [];
    const table = tableMatch[1];

    const setMatch = sql.match(/SET\s+(.+?)\s+WHERE/is);
    const whereMatch = sql.match(/WHERE\s+(\w+)\s*=\s*\?/i);
    if (!setMatch || !whereMatch) return [];

    const setCols = setMatch[1].split(',').map(p => p.trim().replace(/\s*=\s*\?/, '').trim().toLowerCase());
    const body = {};
    setCols.forEach((col, i) => { body[col] = params[i]; });

    const whereCol = whereMatch[1].toLowerCase();
    const whereVal = params[setCols.length];
    const filters = `${whereCol}=eq.${encodeURIComponent(whereVal)}`;

    return await supabaseQuery(table, { method: 'PATCH', body, filters });
  }

  // DELETE queries
  if (sqlUpper.startsWith('DELETE FROM')) {
    const tableMatch = sql.match(/DELETE FROM\s+(\w+)\s+WHERE\s+(\w+)\s*=\s*\?/i);
    if (!tableMatch) return [];
    const table = tableMatch[1];
    const col = tableMatch[2].toLowerCase();
    const filters = `${col}=eq.${encodeURIComponent(params[0])}`;
    return await supabaseQuery(table, { method: 'DELETE', filters });
  }

  return [];
}

function getDbType() {
  if (SUPABASE_SERVICE_KEY && !process.env.DATABASE_URL && !process.env.DB_HOST) return 'supabase';
  if (process.env.DATABASE_URL || process.env.POSTGRES_URL) return 'postgres';
  if (process.env.DB_HOST) return 'mysql';
  return 'memory';
}

module.exports = {
  dbQuery,
  getDbType,
  supabaseQuery,
  memoryStore,
  SUPABASE_URL,
  SUPABASE_SERVICE_KEY
};
