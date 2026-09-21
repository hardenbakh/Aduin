const db = require('../config/db');

const getDashboardData = async (req, res) => {
  try {
    const statsQuery = `
      SELECT 
        COUNT(*) AS total_laporan,
        COUNT(CASE WHEN status = 'Menunggu Verifikasi' THEN 1 END) AS perlu_verifikasi,
        COUNT(CASE WHEN status = 'Diproses' THEN 1 END) AS sedang_diproses,
        COUNT(CASE WHEN status = 'Selesai' THEN 1 END) AS selesai
      FROM pengaduan;
    `;
    const listQuery = `
      SELECT 
        p.id_pengaduan, u.nama AS nama_pelapor, r.nama_ruangan, 
        kf.nama_kategori, p.judul_laporan, p.status, p.foto_bukti, p.tanggal_lapor
      FROM pengaduan p
      JOIN users u ON p.id_pelapor = u.id_user
      JOIN ruangan r ON p.id_ruangan = r.id_ruangan
      JOIN kategori_fasilitas kf ON p.id_fasilitas = kf.id_fasilitas
      ORDER BY p.tanggal_lapor DESC;
    `;

    const stats = await db.query(statsQuery);
    const list = await db.query(listQuery);

    res.json({
      success: true,
      statistik: stats.rows[0],
      laporan: list.rows
    });
  } catch (err) {
    res.status(500).json({ success: false, message: err.message });
  }
};

const verifikasiLaporan = async (req, res) => {
  const { id_pengaduan } = req.params;
  const { id_verifikator, status, alasan_penolakan, id_teknisi } = req.body;

  const client = await db.connect();
  try {
    await client.query('BEGIN');

    await client.query(
      `UPDATE pengaduan 
       SET status = $1, id_verifikator = $2, alasan_penolakan = $3 
       WHERE id_pengaduan = $4`,
      [status, id_verifikator, alasan_penolakan || null, id_pengaduan]
    );

    if (status === 'Diproses' && id_teknisi) {
      await client.query(
        `INSERT INTO penanganan_tugas (id_pengaduan, id_teknisi) 
         VALUES ($1, $2) ON CONFLICT (id_pengaduan) DO UPDATE SET id_teknisi = EXCLUDED.id_teknisi`,
        [id_pengaduan, id_teknisi]
      );
    }

    await client.query(
      `INSERT INTO riwayat_status (id_pengaduan, changed_by, status_baru, keterangan)
       VALUES ($1, $2, $3, $4)`,
      [id_pengaduan, id_verifikator, status, alasan_penolakan || `Tiket diubah ke status ${status}`]
    );

    await client.query('COMMIT');
    res.json({ success: true, message: `Status berhasil diubah menjadi ${status}` });
  } catch (err) {
    await client.query('ROLLBACK');
    res.status(500).json({ success: false, message: err.message });
  } finally {
    client.release();
  }
};

module.exports = { getDashboardData, verifikasiLaporan };