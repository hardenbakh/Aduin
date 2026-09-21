const express = require('express');
const router = express.Router();
const pengaduanController = require('../controllers/pengaduanController');
const upload = require('../middlewares/uploadMiddleware');

router.post('/', upload.single('foto_bukti'), pengaduanController.buatPengaduan);
router.get('/pelapor/:id_pelapor', pengaduanController.getPengaduanByPelapor);

module.exports = router;