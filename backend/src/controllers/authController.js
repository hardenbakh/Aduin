const db = require('../config/db');

const login = async (req, res) => {
  const { email, password } = req.body;
  try {
    const result = await db.query('SELECT * FROM users WHERE email = $1', [email]);
    if (result.rows.length === 0) {
      return res.status(401).json({ success: false, message: 'Email tidak terdaftar' });
    }

    const user = result.rows[0];
    
    // Pengecekan password sederhana (bisa diganti bcrypt.compare untuk hashing)
    if (user.password !== password) {
      return res.status(401).json({ success: false, message: 'Password salah' });
    }

    // Hapus password dari objek respon
    delete user.password;

    res.json({
      success: true,
      message: 'Login berhasil',
      user: user
    });
  } catch (err) {
    res.status(500).json({ success: false, message: err.message });
  }
};

module.exports = { login };