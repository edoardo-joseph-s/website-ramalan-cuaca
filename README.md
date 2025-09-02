# Aplikasi Prakiraan Cuaca Indonesia

Aplikasi web untuk melihat prakiraan cuaca di seluruh wilayah Indonesia menggunakan data dari BMKG (Badan Meteorologi, Klimatologi, dan Geofisika).
![Tampilan Website Ramalan Cuaca](tampilan_web.png)

**Usename: demo**
**Password: demo123**

## 🌟 Fitur Utama

### 🔍 Pencarian Lokasi
- **Autocomplete Search**: Pencarian lokasi dengan fitur autocomplete yang responsif
- **Database Wilayah Lengkap**: Mencakup seluruh desa/kelurahan di Indonesia (tingkat IV)
- **Pencarian Cerdas**: Sistem pencarian yang toleran terhadap typo dan variasi nama

### 🌤️ Informasi Cuaca
- **Data Real-time**: Menggunakan API resmi BMKG untuk data cuaca terkini
- **Prakiraan 3 Hari**: Informasi cuaca untuk hari ini dan 2 hari ke depan
- **Detail Lengkap**: Suhu, kelembaban, kecepatan angin, dan kondisi cuaca
- **Visualisasi Menarik**: Interface yang user-friendly dengan ikon cuaca

### 👤 Sistem User
- **Registrasi & Login**: Sistem autentikasi yang aman
- **Manajemen Session**: Session management yang robust
- **Rate Limiting**: Pembatasan pencarian untuk user guest

### ❤️ Fitur Favorit
- **Simpan Lokasi**: User dapat menyimpan lokasi favorit
- **Akses Cepat**: Lihat cuaca lokasi favorit dengan satu klik
- **Manajemen Favorit**: Tambah dan hapus lokasi favorit dengan mudah

### 📊 Riwayat Pencarian
- **History Tracking**: Menyimpan riwayat pencarian user

## 🛠️ Teknologi yang Digunakan

### Backend
- **PHP 7.4+**: Server-side scripting
- **SQLite**: Database ringan dan portable
- **PDO**: Database abstraction layer untuk keamanan

### Frontend
- **HTML5**: Struktur halaman modern
- **CSS3**: Styling dengan gradient dan animasi
- **JavaScript (Vanilla)**: Interaktivitas tanpa framework
- **Font Awesome**: Icon library

### API & Data
- **BMKG API**: `https://api.bmkg.go.id/publik/prakiraan-cuaca`
- **CSV Data**: Database kode wilayah tingkat IV Indonesia

## 📁 Struktur Proyek

```
ramalan-cuaca/
├── 📄 prakiraan-cuaca.php     # Halaman utama aplikasi
├── 📄 dashboard.php           # Dashboard user
├── 📄 ajax_handler.php        # Handler untuk request AJAX
├── 📄 weather_app.db          # Database SQLite
├── 📄 kode_wilayah_tingkat_iv.csv # Data wilayah Indonesia
│
├── 📁 config/
│   └── 📄 database.php        # Konfigurasi database
│
├── 📁 classes/
│   └── 📄 User.php           # Class untuk manajemen user
│
├── 📁 auth/
│   ├── 📄 login.php          # Halaman login
│   └── 📄 logout.php         # Proses logout
│
├── 📁 api/
│   └── 📄 search_limit_status.php # API status limit pencarian
│
├── 📁 templates/
│   ├── 📄 header.php         # Template header
│   ├── 📄 footer.php         # Template footer
│   ├── 📄 search_form.php    # Form pencarian
│   ├── 📄 search_results.php # Hasil pencarian
│   └── 📄 weather_info.php   # Informasi cuaca
│
├── 📁 css/
│   └── 📄 style.css          # Stylesheet utama
│
└── 📁 js/
    └── 📄 script.js          # JavaScript utama
```

## 🚀 Instalasi

### Persyaratan Sistem
- PHP 7.4 atau lebih tinggi
- SQLite extension untuk PHP
- Web server (Apache/Nginx) atau PHP built-in server

### Langkah Instalasi

1. **Clone atau Download Project**
   ```bash
   git clone https://github.com/edoardo-joseph-s/website-ramalan-cuaca.git
   cd ramalan-cuaca
   ```

2. **Setup Database**
   Database SQLite sudah disertakan (`weather_app.db`). Jika perlu reset:
   ```bash
   # Database akan otomatis dibuat saat pertama kali diakses
   ```

3. **Konfigurasi Web Server**
   
   **Menggunakan PHP Built-in Server:**
   ```bash
   php -S localhost:8000
   ```
   
   **Menggunakan Apache/Nginx:**
   - Arahkan document root ke folder project
   - Pastikan PHP dan SQLite extension aktif

4. **Akses Aplikasi**
   Buka browser dan akses:
   ```
   http://localhost:8000/prakiraan-cuaca.php
   ```

## 👥 Akun Default

Aplikasi sudah dilengkapi dengan akun default untuk testing:

| Username | Password | Role  |
|----------|----------|-------|
| admin    | admin    | Admin |
| demo     | demo123  | User  |

## 📖 Cara Penggunaan

### 🔍 Mencari Cuaca
1. **Tanpa Login (Guest)**:
   - Ketik nama lokasi di search box
   - Pilih dari dropdown autocomplete
   - Lihat informasi cuaca
   - Maksimal 3 pencarian per 3 menit

2. **Dengan Login**:
   - Login terlebih dahulu
   - Pencarian unlimited
   - Dapat menyimpan lokasi favorit
   - Akses riwayat pencarian

### ❤️ Mengelola Favorit
1. **Menambah Favorit**:
   - Cari lokasi yang diinginkan
   - Klik tombol "Tambah ke Favorit" (❤️)
   - Lokasi akan tersimpan di dashboard

2. **Melihat Favorit**:
   - Favorit ditampilkan di halaman utama (jika login)
   - Klik "Lihat Cuaca" untuk melihat prakiraan

3. **Menghapus Favorit**:
   - Klik tombol hapus (🗑️) pada kartu favorit
   - Konfirmasi penghapusan

## 🔧 Konfigurasi

### Database
Konfigurasi database ada di `config/database.php`:
```php
// Path database SQLite
$db_path = __DIR__ . '/../weather_app.db';
```

### Rate Limiting
Pembatasan pencarian untuk guest user:
- **Limit**: 3 pencarian per 3 menit
- **Reset**: Otomatis setiap 3 menit
- **Bypass**: Login untuk unlimited access

### API Configuration
API BMKG yang digunakan:
```php
$api_url = "https://api.bmkg.go.id/publik/prakiraan-cuaca?adm4={$kode_wilayah}";
```

## 🎨 Kustomisasi

### Styling
Edit `css/style.css` untuk mengubah tampilan:
- Color scheme
- Layout responsif
- Animasi dan transisi

### JavaScript
Edit `js/script.js` untuk menambah interaktivitas:
- AJAX handling
- Form validation
- UI enhancements

## 🔒 Keamanan

### Fitur Keamanan
- **SQL Injection Protection**: Menggunakan prepared statements
- **XSS Prevention**: HTML escaping pada output
- **Session Security**: Secure session management
- **Rate Limiting**: Mencegah abuse API
- **Input Validation**: Validasi semua input user

### Best Practices
- Selalu gunakan HTTPS di production
- Regular backup database
- Monitor log untuk aktivitas mencurigakan
- Update PHP dan dependencies secara berkala

## 🐛 Troubleshooting

### Masalah Umum

1. **Database Error**
   ```
   Error: SQLSTATE[HY000] [14] unable to open database file
   ```
   **Solusi**: Pastikan file `weather_app.db` memiliki permission yang benar

2. **API Error**
   ```
   ERROR: Gagal mengambil data
   ```
   **Solusi**: Periksa koneksi internet dan status API BMKG

3. **Session Error**
   ```
   Warning: session_start(): Cannot send session cookie
   ```
   **Solusi**: Pastikan tidak ada output sebelum session_start()

### Debug Mode
Untuk debugging, uncomment bagian ini di `prakiraan-cuaca.php`:
```php
echo "<pre>";
print_r($data);
echo "</pre>";
```

## 📊 Database Schema

### Tabel Users
```sql
CREATE TABLE users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP,
    failed_login_attempts INTEGER DEFAULT 0,
    last_failed_login TIMESTAMP
);
```

### Tabel Favorite Locations
```sql
CREATE TABLE favorite_locations (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    location_name VARCHAR(255) NOT NULL,
    latitude DECIMAL(10, 8),
    longitude DECIMAL(11, 8),
    kecamatan VARCHAR(100),
    kota VARCHAR(100),
    provinsi VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);
```

### Tabel Search History
```sql
CREATE TABLE search_history (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER,
    location_name VARCHAR(255),
    latitude DECIMAL(10, 8),
    longitude DECIMAL(11, 8),
    kecamatan VARCHAR(100),
    kota VARCHAR(100),
    provinsi VARCHAR(100),
    search_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ip_address VARCHAR(45)
);
```

## 📝 License

Project ini menggunakan MIT License. Lihat file `LICENSE` untuk detail lengkap.

## 🙏 Acknowledgments

- **BMKG**: Untuk menyediakan API cuaca gratis
- **Font Awesome**: Untuk icon library
- **PHP Community**: Untuk dokumentasi dan support

---

**Dibuat dengan ❤️ untuk Indonesia** 🇮🇩
