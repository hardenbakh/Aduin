<?php
class AuthController {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function login() {
        // Ambil data JSON dari request body
        $data = json_decode(file_get_contents("php://input"), true);

        $email = $data['email'] ?? '';
        $password = $data['password'] ?? '';

        if (empty($email) || empty($password)) {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "Email dan password wajib diisi."]);
            return;
        }

        try {
            $query = "SELECT * FROM users WHERE email = :email LIMIT 1";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(":email", $email);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // Verifikasi password (mendukung hash atau teks biasa tergantung database uji)
            if ($user && ($password === $user['password'] || password_verify($password, $user['password']))) {
                echo json_encode([
                    "success" => true,
                    "message" => "Login berhasil",
                    "data" => [
                        "id_user" => $user['id_user'],
                        "nama" => $user['nama'],
                        "email" => $user['email'],
                        "role" => $user['role'] // mahasiswa, dosen, sekjur, teknisi
                    ]
                ]);
            } else {
                http_response_code(401);
                echo json_encode(["success" => false, "message" => "Email atau password salah."]);
            }
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["success" => false, "message" => "Kesalahan server: " . $e->getMessage()]);
        }
    }
}
?>