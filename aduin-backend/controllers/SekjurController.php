<?php
class SekjurController {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function getDashboardData() {
        try {
            // Ambil semua daftar pengaduan beserta relasi ruangan dan kategori
            $query = "SELECT p.*, r.nama_ruangan, k.nama_kategori, u.nama as nama_pelapor
                      FROM pengaduan p
                      JOIN ruangan r ON p.id_ruangan = r.id_ruangan
                      JOIN kategori_fasilitas k ON p.id_kategori = k.id_fasilitas
                      JOIN users u ON p.id_pelapor = u.id_user
                      ORDER BY p.tanggal_lapor DESC";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode(["success" => true, "data" => $result]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["success" => false, "message" => "Gagal memuat dasbor sekjur: " . $e->getMessage()]);
        }
    }

    public function verifikasiLaporan($id_pengaduan) {
        $data = json_decode(file_get_contents("php://input"), true);
        $status_baru = $data['status'] ?? ''; // Disetujui, Ditolak, Diteruskan ke Pusat
        $id_sekjur = $data['id_sekjur'] ?? null;
        $keterangan = $data['keterangan'] ?? 'Verifikasi oleh Sekertaris Jurusan';

        $valid_statuses = ['Disetujui', 'Ditolak', 'Diteruskan ke Pusat', 'Diproses'];
        if (!in_array($status_baru, $valid_statuses)) {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "Status verifikasi tidak valid."]);
            return;
        }

        try {
            $this->db->beginTransaction();

            // 1. Update status di tabel pengaduan
            $query = "UPDATE pengaduan SET status = :status, id_sekjur = :id_sekjur WHERE id_pengaduan = :id_pengaduan";
            $stmt = $this->db->prepare($query);
            $stmt->execute([
                ':status' => $status_baru,
                ':id_sekjur' => $id_sekjur,
                ':id_pengaduan' => $id_pengaduan
            ]);

            // 2. Rekam ke riwayat status
            $queryRiwayat = "INSERT INTO riwayat_status (id_pengaduan, status_baru, keterangan, timestamp) 
                             VALUES (:id_pengaduan, :status_baru, :keterangan, NOW())";
            $stmtRiwayat = $this->db->prepare($queryRiwayat);
            $stmtRiwayat->execute([
                ':id_pengaduan' => $id_pengaduan,
                ':status_baru' => $status_baru,
                ':keterangan' => $keterangan
            ]);

            $this->db->commit();

            echo json_encode(["success" => true, "message" => "Status pengaduan berhasil diperbarui menjadi: " . $status_baru]);
        } catch (Exception $e) {
            $this->db->rollBack();
            http_response_code(500);
            echo json_encode(["success" => false, "message" => "Gagal memverifikasi laporan: " . $e->getMessage()]);
        }
    }
}
?>