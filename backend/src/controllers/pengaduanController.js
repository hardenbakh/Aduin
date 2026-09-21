const db = require('../config/db');

const buatPengaduan = async (req, res) => {
  const { id_pelapor, id_ruangan, id_fasilitas, judul_laporan, deskripsi } = req.body;
  const foto_bukti = req.file ? req.file.filename : null;

  const client = await db.connect();
  try {
    await client.query('BEGIN');

    const insertQuery = `
      INSERT INTO pengaduan (id_pelapor, id_ruangan, id_fasilitas, judul_laporan, deskripsi, foto_bukti)
      VALUES ($1, $2, $3, $4, $5, $6) RETURNING *;
    `;
    const result = await client.query(insertQuery, [id_pelapor, id_ruangan, id_fasilitas, judul_laporan, deskripsi, foto_bukti]);
    const pengaduanBaru = result.rows[0];

    // Catat ke riwayat_status
    await client.query(
      `INSERT INTO riwayat_status (id_pengaduan, changed_by, status_baru, keterangan)
       VALUES ($1, $2, 'Menunggu Verifikasi', 'Pengaduan baru dibuat')`,
      [pengaduanBaru.id_pengaduan, id_pelapor]
    );

    await client.query('COMMIT');
    res.status(201).json({ success: true, message: 'Laporan pengaduan berhasil dikirim', data: pengaduanBaru });
  } catch (err) {
    await client.query('ROLLBACK');
    res.status(500).json({ success: false, message: err.message });
  } finally {
    client.release();
  }
};

const getPengaduanByPelapor = async (req, res) => {
  const { id_pelapor } = req.params;
  try {
    const query = `
      SELECT p.*, r.nama_ruangan, kf.nama_kategori 
      FROM pengaduan p
      JOIN ruangan r ON p.id_ruangan = r.id_ruangan
      JOIN kategori_fasilitas kf ON p.id_fasilitas = kf.id_fasilitas
      WHERE p.id_pelapor = $1
      ORDER BY p.tanggal_lapor DESC;
    `;
    const result = await db.query(query, [id_pelapor]);
    res.json({ success: true, data: result.rows });
  } catch (err) {
    res.status(500).json({ success: false, message: err.message });
  }
};

module.exports = { buatPengaduan, getPengaduanByPelapor };