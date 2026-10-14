<?php
class MasterController {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    // Ambil data ruangan
    public function getRuangan() {
        try {
            $query = "SELECT * FROM ruangan ORDER BY id_ruangan ASC";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode([
                "success" => true,
                "data" => $result
            ]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode([
                "success" => false,
                "message" => "Gagal mengambil data ruangan: " . $e->getMessage()
            ]);
        }
    }

    // Ambil data kategori fasilitas
    public function getKategoriFasilitas() {
        try {
            $query = "SELECT * FROM kategori_fasilitas ORDER BY id_fasilitas ASC";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode([
                "success" => true,
                "data" => $result
            ]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode([
                "success" => false,
                "message" => "Gagal mengambil data kategori fasilitas: " . $e->getMessage()
            ]);
        }
    }

    // Ambil data mahasiswa
    public function getMahasiswa() {
        try {
            $query = "SELECT * FROM mahasiswa";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode([
                "success" => true,
                "data" => $result
            ]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode([
                "success" => false,
                "message" => "Gagal mengambil data mahasiswa: " . $e->getMessage()
            ]);
        }
    }

    // Ambil data dosen
    public function getDosen() {
        try {
            $query = "SELECT * FROM dosen";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode([
                "success" => true,
                "data" => $result
            ]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode([
                "success" => false,
                "message" => "Gagal mengambil data dosen: " . $e->getMessage()
            ]);
        }
    }

    // Ambil data teknisi
    public function getTeknisi() {
        try {
            $query = "SELECT * FROM teknisi";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode([
                "success" => true,
                "data" => $result
            ]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode([
                "success" => false,
                "message" => "Gagal mengambil data teknisi: " . $e->getMessage()
            ]);
        }
    }
}