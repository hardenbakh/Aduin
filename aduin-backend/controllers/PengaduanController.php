<?php
class PengaduanController {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function buatPengaduan() {
        // Mendukung form-data (karena ada upload file gambar)
        $id_pelapor = $_POST['id_pelapor'] ?? null;
        $id_ruangan = $_POST['id_ruangan'] ?? null;
        $id_kategori = $_POST['id_kategori'] ?? null;
        $judul_keluhan = $_POST['judul_keluhan'] ?? '';
        $deskripsi = $_POST['deskripsi'] ?? '';
        
        $foto_bukti = null;

        // Tangani unggah file foto bukti
        if (isset($_FILES['foto_bukti']) && $_FILES['foto_bukti']['error'] === UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['foto_bukti']['tmp_name'];
            $fileName = $_FILES['foto_bukti']['name'];
            $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
            if (in_array($fileExtension, $allowedExtensions)) {
                $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
                $uploadFileDir = __DIR__ . '/../uploads/';
                
                if (!is_dir($uploadFileDir)) {
                    mkdir($uploadFileDir, 0755, true);
                }
                
                $dest_path = $uploadFileDir . $newFileName;
                if(move_uploaded_file($fileTmpPath, $dest_path)) {
                    $foto_bukti = 'uploads/' . $newFileName;
                }
            }
        }

        if (!$id_pelapor || !$id_ruangan || !$id_kategori || empty($judul_keluhan)) {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "Data pengaduan tidak lengkap."]);
            return;
        }

        try {
            $this->db->beginTransaction();

            // 1. Insert ke tabel pengaduan
            $query = "INSERT INTO pengaduan (id_pelapor, id_ruangan, id_kategori, judul_keluhan, deskripsi, foto_bukti, status, tanggal_lapor) 
                      VALUES (:id_pelapor, id_ruangan, :id_kategori, :judul_keluhan, :deskripsi, :foto_bukti, 'Menunggu Verifikasi', NOW()) RETURNING id_pengaduan";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute([
                ':id_pelapor' => $id_pelapor,
                ':id_ruangan' => $id_ruangan,
                ':id_kategori' => $id_kategori,
                ':judul_keluhan' => $judul_keluhan,
                ':deskripsi' => $deskripsi,
                ':foto_bukti' => $foto_bukti
            ]);
            
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $id_pengaduan = $row['id_pengaduan'];

            // 2. Catat riwayat status awal
            $queryRiwayat = "INSERT INTO riwayat_status (id_pengaduan, status_baru, keterangan, timestamp) 
                             VALUES (:id_pengaduan, 'Menunggu Verifikasi', 'Laporan berhasil dikirim oleh pelapor.', NOW())";
            $stmtRiwayat = $this->db->prepare($queryRiwayat);
            $stmtRiwayat->execute([
                ':id_pengaduan' => $id_pengaduan
            ]);

            $this->db->commit();

            echo json_encode([
                "success" => true,
                "message" => "Pengaduan berhasil dikirim.",
                "id_pengaduan" => $id_pengaduan
            ]);

        } catch (Exception $e) {
            $this->db->rollBack();
            http_response_code(500);
            echo json_encode(["success" => false, "message" => "Gagal menyimpan pengaduan: " . $e->getMessage()]);
        }
    }

    public function getPengaduanByPelapor($id_pelapor) {
        try {
            $query = "SELECT p.*, r.nama_ruangan, k.nama_kategori 
                      FROM pengaduan p 
                      JOIN ruangan r ON p.id_ruangan = r.id_ruangan
                      JOIN kategori_fasilitas k ON p.id_kategori = k.id_fasilitas
                      WHERE p.id_pelapor = :id_pelapor 
                      ORDER BY p.tanggal_lapor DESC";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(":id_pelapor", $id_pelapor);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode(["success" => true, "data" => $result]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["success" => false, "message" => "Gagal mengambil data: " . $e->getMessage()]);
        }
    }
}
?>