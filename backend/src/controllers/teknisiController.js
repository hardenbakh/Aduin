const db = require('../config/db');

const getTugasTeknisi = async (req, res) => {
  const { id_teknisi } = req.params;
  try {
    const query = `
      SELECT 
        pt.id_penanganan, p.id_pengaduan, p.judul_laporan, p.deskripsi, 
        p.foto_bukti, r.nama_ruangan, kf.nama_kategori, u.nama AS nama_pelapor, 
        p.status, pt.waktu_mulai
      FROM penanganan_tugas pt
      JOIN pengaduan p ON pt.id_pengaduan = p.id_pengaduan
      JOIN ruangan r ON p.id_ruangan = r.id_ruangan
      JOIN kategori_fasilitas kf ON p.id_fasilitas = kf.id_fasilitas
      JOIN users u ON p.id_pelapor = u.id_user
      WHERE pt.id_teknisi = $1 AND p.status = 'Diproses';
    `;
    const result = await db.query(query, [id_teknisi]);
    res.json({ success: true, data: result.rows });
  } catch (err) {
    res.status(500).json({ success: false, message: err.message });
  }
};

const selesaikanTugas = async (req, res) => {
  const { id_pengaduan } = req.params;
  const { id_teknisi, catatan_teknisi } = req.body;
  const foto_hasil = req.file ? req.file.filename : null;

  const client = await db.connect();
  try {
    await client.query('BEGIN');

    await client.query(
      `UPDATE penanganan_tugas 
       SET waktu_selesai = NOW(), foto_hasil = $1, catatan_teknisi = $2 
       WHERE id_pengaduan = $3`,
      [foto_hasil, catatan_teknisi, id_pengaduan]
    );

    await client.query(
      `UPDATE pengaduan SET status = 'Selesai' WHERE id_pengaduan = $1`,
      [id_pengaduan]
    );

    await client.query(
      `INSERT INTO riwayat_status (id_pengaduan, changed_by, status_baru, keterangan)
       VALUES ($1, $2, 'Selesai', $3)`,
      [id_pengaduan, id_teknisi, catatan_teknisi]
    );

    await client.query('COMMIT');
    res.json({ success: true, message: 'Tugas perbaikan berhasil diselesaikan' });
  } catch (err) {
    await client.query('ROLLBACK');
    res.status(500).json({ success: false, message: err.message });
  } finally {
    client.release();
  }
};

module.exports = { getTugasTeknisi, selesaikanTugas };