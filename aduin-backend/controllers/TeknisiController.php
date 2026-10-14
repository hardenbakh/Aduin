<?php
class TeknisiController {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function getTugasTeknisi($id_teknisi) {
        try {
            // Mengambil pengaduan yang sudah disetujui sekjur untuk dikerjakan teknisi
            $query = "SELECT p.*, r.nama_ruangan, k.nama_kategori 
                      FROM pengaduan p
                      JOIN ruangan r ON p.id_ruangan = r.id_ruangan
                      JOIN kategori_fasilitas k ON p.id_kategori = k.id_fasilitas
                      WHERE p.status IN ('Disetujui', 'Diproses')
                      ORDER BY p.tanggal_lapor ASC";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode(["success" => true, "data" => $result]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["success" => false, "message" => "Gagal memuat tugas teknisi: " . $e->getMessage()]);
        }
    }

    public function selesaikanTugas($id_pengaduan) {
        $id_teknisi = $_POST['id_teknisi'] ?? null;
        $catatan_teknis = $_POST['catatan_teknis'] ?? 'Perbaikan selesai dilakukan.';
        $foto_hasil = null;

        // Tangani unggah foto bukti hasil perbaikan
        if (isset($_FILES['foto_hasil']) && $_FILES['foto_hasil']['error'] === UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['foto_hasil']['tmp_name'];
            $fileName = $_FILES['foto_hasil']['name'];
            $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
            if (in_array($fileExtension, $allowedExtensions)) {
                $newFileName = 'hasil_' . md5(time() . $fileName) . '.' . $fileExtension;
                $uploadFileDir = __DIR__ . '/../uploads/';
                
                if (!is_dir($uploadFileDir)) {
                    mkdir($uploadFileDir, 0755, true);
                }
                
                if(move_uploaded_file($fileTmpPath, $uploadFileDir . $newFileName)) {
                    $foto_hasil = 'uploads/' . $newFileName;
                }
            }
        }

        try {
            $this->db->beginTransaction();

            // 1. Masukkan data ke tabel penanganan_tugas
            $queryTugas = "INSERT INTO penanganan_tugas (id_pengaduan, id_teknisi, tanggal_mulai, waktu_selesai, foto_hasil, catatan_teknis) 
                           VALUES (:id_pengaduan, :id_teknisi, NOW(), NOW(), :foto_hasil, :catatan_teknis)";
            $stmtTugas = $this->db->prepare($queryTugas);
            $stmtTugas->execute([
                ':id_pengaduan' => $id_pengaduan,
                ':id_teknisi' => $id_teknisi,
                ':foto_hasil' => $foto_hasil,
                ':catatan_teknis' => $catatan_teknis
            ]);

            // 2. Ubah status pengaduan menjadi 'Selesai'
            $queryUp = "UPDATE pengaduan SET status = 'Selesai' WHERE id_pengaduan = :id_pengaduan";
            $stmtUp = $this->db->prepare($queryUp);
            $stmtUp->execute([':id_pengaduan' => $id_pengaduan]);

            // 3. Catat riwayat status
            $queryRiwayat = "INSERT INTO riwayat_status (id_pengaduan, status_baru, keterangan, timestamp) 
                             VALUES (:id_pengaduan, 'Selesai', :keterangan, NOW())";
            $stmtRiwayat = $this->db->prepare($queryRiwayat);
            $stmtRiwayat->execute([
                ':id_pengaduan' => $id_pengaduan,
                ':keterangan' => $catatan_teknis
            ]);

            $this->db->commit();

            echo json_encode(["success" => true, "message" => "Tugas berhasil diselesaikan dan status diperbarui."]);
        } catch (Exception $e) {
            $this->db->rollBack();
            http_response_code(500);
            echo json_encode(["success" => false, "message" => "Gagal memperbarui status tugas: " . $e->getMessage()]);
        }
    }
}
?>