<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");

require_once 'config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();

    if ($db) {
        // Cek versi PostgreSQL untuk memastikan query berhasil dijalankan
        $stmt = $db->query("SELECT version()");
        $version = $stmt->fetchColumn();

        http_response_code(200);
        echo json_encode([
            "success" => true,
            "message" => "Berhasil terhubung ke database PostgreSQL!",
            "database_version" => $version
        ], JSON_PRETTY_PRINT);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Gagal terhubung ke database.",
        "error" => $e->getMessage()
    ], JSON_PRETTY_PRINT);
}
?>