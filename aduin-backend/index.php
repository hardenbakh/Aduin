<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PATCH, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once 'config/database.php';
require_once 'controllers/MasterController.php';
require_once 'controllers/AuthController.php';
require_once 'controllers/PengaduanController.php';
require_once 'controllers/SekjurController.php';
require_once 'controllers/TeknisiController.php';

$database = new Database();
$db = $database->getConnection();

$request_uri = $_SERVER['REQUEST_URI'];

// Penanganan path dinamis untuk lokal (Laragon) maupun produksi
$path = '';
$parsed_url = parse_url($request_uri, PHP_URL_PATH);

if (strpos($parsed_url, 'api/') !== false) {
    $path = '/' . substr($parsed_url, strpos($parsed_url, 'api/'));
} else {
    $path = $parsed_url;
}

$method = $_SERVER['REQUEST_METHOD'];

// ==========================================
// ROUTING API PENGADUAN & MASTER DATA
// ==========================================

// Master Data Endpoints
if ($path === '/api/master/ruangan' && $method === 'GET') {
    (new MasterController($db))->getRuangan();
} elseif ($path === '/api/master/kategori-fasilitas' && $method === 'GET') {
    (new MasterController($db))->getKategoriFasilitas();
} elseif ($path === '/api/master/mahasiswa' && $method === 'GET') {
    (new MasterController($db))->getMahasiswa();
} elseif ($path === '/api/master/dosen' && $method === 'GET') {
    (new MasterController($db))->getDosen();
} elseif ($path === '/api/master/teknisi' && $method === 'GET') {
    (new MasterController($db))->getTeknisi();

// Auth & Pengaduan Endpoints
} elseif ($path === '/api/auth/login' && $method === 'POST') {
    (new AuthController($db))->login();
} elseif ($path === '/api/pengaduan' && $method === 'POST') {
    (new PengaduanController($db))->buatPengaduan();
} elseif (preg_match('/^\/api\/pengaduan\/pelapor\/(\d+)$/', $path, $matches) && $method === 'GET') {
    (new PengaduanController($db))->getPengaduanByPelapor($matches[1]);
} elseif ($path === '/api/sekjur/dashboard' && $method === 'GET') {
    (new SekjurController($db))->getDashboardData();
} elseif (preg_match('/^\/api\/sekjur\/verifikasi\/(\d+)$/', $path, $matches) && $method === 'PATCH') {
    (new SekjurController($db))->verifikasiLaporan($matches[1]);
} elseif (preg_match('/^\/api\/teknisi\/tugas\/(\d+)$/', $path, $matches) && $method === 'GET') {
    (new TeknisiController($db))->getTugasTeknisi($matches[1]);
} elseif (preg_match('/^\/api\/teknisi\/selesai\/(\d+)$/', $path, $matches) && $method === 'POST') {
    (new TeknisiController($db))->selesaikanTugas($matches[1]);
} else {
    http_response_code(404);
    echo json_encode(["success" => false, "message" => "Endpoint tidak ditemukan", "debug_path" => $path]);
}
?>