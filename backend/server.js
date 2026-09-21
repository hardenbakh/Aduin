const express = require('express');
const cors = require('cors');
const path = require('path');
require('dotenv').config();

// Import Routes
const authRoutes = require('./src/routes/authRoutes');
const masterRoutes = require('./src/routes/masterRoutes');
const pengaduanRoutes = require('./src/routes/pengaduanRoutes');
const sekjurRoutes = require('./src/routes/sekjurRoutes');
const teknisiRoutes = require('./src/routes/teknisiRoutes');

const app = express();

// Middlewares Global
app.use(cors());
app.use(express.json());
app.use(express.urlencoded({ extended: true }));

// Akses Static File Gambar Uploads
app.use('/uploads', express.static(path.join(__dirname, 'uploads')));

// Routing API
app.use('/api/auth', authRoutes);
app.use('/api/master', masterRoutes);
app.use('/api/pengaduan', pengaduanRoutes);
app.use('/api/sekjur', sekjurRoutes);
app.use('/api/teknisi', teknisiRoutes);

// Root Route Test
app.get('/', (req, res) => {
  res.json({ message: 'Welcome to Aduin REST API Server' });
});

// Jalankan Server
const PORT = process.env.PORT || 5000;
app.listen(PORT, () => {
  console.log(`Server Backend Aduin berjalan pada port ${PORT}`);
});