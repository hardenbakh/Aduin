const express = require('express');
const router = express.Router();
const teknisiController = require('../controllers/teknisiController');
const upload = require('../middlewares/uploadMiddleware');

router.get('/tugas/:id_teknisi', teknisiController.getTugasTeknisi);
router.patch('/selesai/:id_pengaduan', upload.single('foto_hasil'), teknisiController.selesaikanTugas);

module.exports = router;