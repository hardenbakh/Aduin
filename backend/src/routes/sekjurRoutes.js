const express = require('express');
const router = express.Router();
const sekjurController = require('../controllers/sekjurController');

router.get('/dashboard', sekjurController.getDashboardData);
router.patch('/verifikasi/:id_pengaduan', sekjurController.verifikasiLaporan);

module.exports = router;