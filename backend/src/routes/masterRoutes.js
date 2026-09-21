const express = require('express');
const router = express.Router();
const masterController = require('../controllers/masterController');

router.get('/ruangan', masterController.getAllRuangan);
router.get('/kategori', masterController.getAllKategori);

module.exports = router;