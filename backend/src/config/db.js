const { Pool } = require('pg');
require('dotenv').config();

const pool = new Pool({
  host: process.env.DB_HOST || 'localhost',
  user: process.env.DB_USER || 'postgres',
  password: String(process.env.DB_PASSWORD || ''), // Memastikan password selalu bernilai String
  database: process.env.DB_NAME || 'aduin_db',
  port: Number(process.env.DB_PORT) || 5432,
});

pool.on('connect', () => {
  console.log('Terhubung ke database PostgreSQL Aduin');
});

pool.on('error', (err) => {
  console.error('Error koneksi PostgreSQL:', err);
});

module.exports = pool;