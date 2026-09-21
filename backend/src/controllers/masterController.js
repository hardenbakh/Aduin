const db = require('../config/db');

const getAllRuangan = async (req, res) => {
  try {
    const result = await db.query('SELECT * FROM ruangan ORDER BY lantai, id_ruangan');
    res.json({ success: true, data: result.rows });
  } catch (err) {
    res.status(500).json({ success: false, message: err.message });
  }
};

const getAllKategori = async (req, res) => {
  try {
    const result = await db.query('SELECT * FROM kategori_fasilitas ORDER BY id_fasilitas');
    res.json({ success: true, data: result.rows });
  } catch (err) {
    res.status(500).json({ success: false, message: err.message });
  }
};

module.exports = { getAllRuangan, getAllKategori };