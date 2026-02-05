# Gerai Tano Batak - E-commerce Application

Aplikasi e-commerce lengkap untuk penjualan ulos tradisional Batak yang dibuat dengan PHP, MySQL, dan Bootstrap.

## Fitur Utama

### Untuk Customer:
- **Registrasi dan Login** - Sistem autentikasi pengguna
- **Katalog Produk** - Browsing produk dengan filter dan pencarian
- **Detail Produk** - Informasi lengkap produk dengan gambar
- **Keranjang Belanja** - Menambah, mengubah, dan menghapus item
- **Checkout** - Proses pemesanan dengan informasi pengiriman
- **Riwayat Pesanan** - Melihat status dan detail pesanan
- **Profil Pengguna** - Mengelola informasi pribadi

### Untuk Admin:
- **Dashboard** - Overview statistik dan data penting
- **Manajemen Produk** - CRUD produk dengan upload gambar
- **Manajemen Kategori** - Mengelola kategori produk
- **Manajemen Pesanan** - Melihat dan mengupdate status pesanan
- **Data Customer** - Informasi dan statistik customer
- **Laporan Penjualan** - Analisis penjualan dan statistik

## Teknologi yang Digunakan

- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+
- **Frontend**: HTML5, CSS3, JavaScript
- **Framework CSS**: Bootstrap 5.1.3
- **Icons**: Font Awesome 6.0
- **Server**: Apache/Nginx

## Instalasi

### 1. Persiapan Environment
```bash
# Pastikan PHP dan MySQL sudah terinstall
php --version
mysql --version
```

### 2. Clone atau Download Project
```bash
# Jika menggunakan Git
git clone [repository-url]

# Atau download dan extract ke folder web server
# Contoh: C:\xampp\htdocs\ulos (untuk XAMPP)
```

### 3. Setup Database
1. Buat database MySQL baru:
```sql
CREATE DATABASE ulos_ecommerce;
```

2. Jalankan setup database:
```
http://localhost/ulos/setup_database.php
```

3. **PENTING**: Hapus file `setup_database.php` setelah setup selesai untuk keamanan.

### 4. Konfigurasi
Edit file `includes/config.php` sesuai dengan environment Anda:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
define('DB_NAME', 'ulos_ecommerce');
define('SITE_URL', 'http://localhost/ulos');
```

### 5. Permissions
Pastikan folder `uploads/` memiliki permission write:
```bash
chmod 755 uploads/
```

## Akun Default

Setelah setup database, akun admin default akan dibuat:
- **Username**: admin
- **Password**: admin123
- **Role**: Administrator

**PENTING**: Ubah password default setelah login pertama!

## Struktur Direktori

```
ulos/
├── admin/                  # Panel admin
│   ├── includes/          # Header/footer admin
│   ├── index.php          # Dashboard admin
│   ├── products.php       # Manajemen produk
│   ├── orders.php         # Manajemen pesanan
│   ├── categories.php     # Manajemen kategori
│   ├── customers.php      # Data customer
│   └── reports.php        # Laporan penjualan
├── assets/                # Asset statis
│   ├── css/              # File CSS
│   ├── js/               # File JavaScript
│   └── images/           # Gambar aplikasi
├── includes/             # File include
│   ├── config.php        # Konfigurasi database
│   ├── header.php        # Header template
│   └── footer.php        # Footer template
├── uploads/              # Upload gambar produk
├── index.php             # Halaman utama
├── products.php          # Katalog produk
├── product_detail.php    # Detail produk
├── cart.php              # Keranjang belanja
├── checkout.php          # Proses checkout
├── login.php             # Halaman login
├── register.php          # Halaman registrasi
├── profile.php           # Profil pengguna
├── orders.php            # Riwayat pesanan
├── about.php             # Tentang kami
├── contact.php           # Kontak
└── README.md             # Dokumentasi
```

## Database Schema

### Tabel Utama:
- **users** - Data pengguna (customer & admin)
- **categories** - Kategori produk
- **products** - Data produk
- **cart** - Keranjang belanja
- **orders** - Data pesanan
- **order_items** - Detail item pesanan

## Fitur Keamanan

- **Password Hashing** - Menggunakan PHP password_hash()
- **SQL Injection Protection** - Prepared statements
- **XSS Protection** - Input sanitization
- **Session Management** - Secure session handling
- **File Upload Validation** - Validasi tipe dan ukuran file

## Customization

### Menambah Kategori Baru
1. Login sebagai admin
2. Masuk ke menu "Kategori"
3. Klik "Tambah Kategori"
4. Isi nama dan deskripsi kategori

### Menambah Produk Baru
1. Login sebagai admin
2. Masuk ke menu "Produk"
3. Klik "Tambah Produk"
4. Isi informasi produk dan upload gambar

### Mengubah Tampilan
- Edit file CSS di `assets/css/style.css`
- Modifikasi template di `includes/header.php` dan `includes/footer.php`

## Troubleshooting

### Error Database Connection
- Periksa konfigurasi di `includes/config.php`
- Pastikan MySQL service berjalan
- Cek username dan password database

### Upload Gambar Gagal
- Periksa permission folder `uploads/`
- Pastikan ukuran file tidak melebihi limit PHP
- Cek format file yang diupload

### Session Error
- Pastikan session_start() dipanggil
- Periksa konfigurasi session di PHP

## Pengembangan Lanjutan

### Fitur yang Bisa Ditambahkan:
- Payment gateway integration
- Email notification system
- Product reviews and ratings
- Wishlist functionality
- Inventory management
- Multi-language support
- Mobile app API

### Optimisasi:
- Implementasi caching
- Image optimization
- Database indexing
- CDN integration

## Support

Untuk pertanyaan atau bantuan:
- Email: support@ulosonline.com
- WhatsApp: +62 812-3456-7890

## License

Project ini dibuat untuk keperluan edukasi dan pembelajaran. Silakan gunakan dan modifikasi sesuai kebutuhan.

---

**Catatan**: Aplikasi ini dibuat sebagai contoh implementasi e-commerce sederhana. Untuk penggunaan production, pastikan untuk menambahkan fitur keamanan tambahan dan optimisasi performa."# ulos" 
