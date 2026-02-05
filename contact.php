<?php
require_once 'includes/config.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = clean_input($_POST['name']);
    $email = clean_input($_POST['email']);
    $subject = clean_input($_POST['subject']);
    $message = clean_input($_POST['message']);
    
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $error = 'Semua field harus diisi';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid';
    } else {
        // Dalam implementasi nyata, Anda bisa mengirim email atau menyimpan ke database
        $success = 'Pesan Anda berhasil dikirim. Kami akan segera menghubungi Anda.';
        // Reset form
        $_POST = array();
    }
}

$page_title = "Kontak Kami";
include 'includes/header.php';
?>

<!-- Page Header -->
<div class="container">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="pt-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Beranda</a></li>
            <li class="breadcrumb-item active">Kontak</li>
        </ol>
    </nav>
    
    <!-- Page Title -->
    <div class="page-header text-center py-4">
        <h1 class="page-title">Hubungi Kami</h1>
        <p class="page-subtitle">Kami siap membantu Anda dengan pertanyaan atau kebutuhan ulos Anda</p>
    </div>
</div>

<!-- Contact Section -->
<section class="py-4">
    <div class="container">
        <div class="row">
            <!-- Contact Form -->
            <div class="col-lg-8 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h4 class="mb-0">Kirim Pesan</h4>
                    </div>
                    <div class="card-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        
                        <?php if ($success): ?>
                            <div class="alert alert-success"><?php echo $success; ?></div>
                        <?php endif; ?>
                        
                        <form method="POST" class="needs-validation" novalidate>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="name" class="form-label">Nama Lengkap *</label>
                                    <input type="text" class="form-control" id="name" name="name" 
                                           value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>" 
                                           required>
                                    <div class="invalid-feedback">
                                        Nama lengkap harus diisi
                                    </div>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">Email *</label>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" 
                                           required>
                                    <div class="invalid-feedback">
                                        Email harus diisi dengan format yang benar
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="subject" class="form-label">Subjek *</label>
                                <select class="form-select" id="subject" name="subject" required>
                                    <option value="">Pilih subjek</option>
                                    <option value="Pertanyaan Produk" <?php echo (isset($_POST['subject']) && $_POST['subject'] == 'Pertanyaan Produk') ? 'selected' : ''; ?>>Pertanyaan Produk</option>
                                    <option value="Pemesanan Khusus" <?php echo (isset($_POST['subject']) && $_POST['subject'] == 'Pemesanan Khusus') ? 'selected' : ''; ?>>Pemesanan Khusus</option>
                                    <option value="Keluhan" <?php echo (isset($_POST['subject']) && $_POST['subject'] == 'Keluhan') ? 'selected' : ''; ?>>Keluhan</option>
                                    <option value="Kerjasama" <?php echo (isset($_POST['subject']) && $_POST['subject'] == 'Kerjasama') ? 'selected' : ''; ?>>Kerjasama</option>
                                    <option value="Lainnya" <?php echo (isset($_POST['subject']) && $_POST['subject'] == 'Lainnya') ? 'selected' : ''; ?>>Lainnya</option>
                                </select>
                                <div class="invalid-feedback">
                                    Subjek harus dipilih
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="message" class="form-label">Pesan *</label>
                                <textarea class="form-control" id="message" name="message" rows="6" 
                                          placeholder="Tulis pesan Anda di sini..." required><?php echo isset($_POST['message']) ? htmlspecialchars($_POST['message']) : ''; ?></textarea>
                                <div class="invalid-feedback">
                                    Pesan harus diisi
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-paper-plane"></i> Kirim Pesan
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Contact Info -->
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Informasi Kontak</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-4">
                            <h6><i class="fas fa-map-marker-alt text-primary me-2"></i> Alamat</h6>
                            <p class="text-muted">
                                Jl. Sisingamangaraja No. 123<br>
                                Medan, Sumatera Utara 20212<br>
                                Indonesia
                            </p>
                        </div>
                        
                        <div class="mb-4">
                            <h6><i class="fas fa-phone text-primary me-2"></i> Telepon</h6>
                            <p class="text-muted">
                                <a href="tel:+6281317975623" class="text-decoration-none">+62 813 17975623</a><br>
                                <a href="tel:+62617654321" class="text-decoration-none">+62 61-765-4321</a>
                            </p>
                        </div>
                        
                        <div class="mb-4">
                            <h6><i class="fas fa-envelope text-primary me-2"></i> Email</h6>
                            <p class="text-muted">
                                <a href="mailto:info@ulosonline.com" class="text-decoration-none">info@ulosonline.com</a><br>
                                <a href="mailto:support@ulosonline.com" class="text-decoration-none">support@ulosonline.com</a>
                            </p>
                        </div>
                        
                        <div class="mb-4">
                            <h6><i class="fas fa-clock text-primary me-2"></i> Jam Operasional</h6>
                            <p class="text-muted">
                                Senin - Jumat: 08:00 - 17:00<br>
                                Sabtu: 08:00 - 15:00<br>
                                Minggu: Tutup
                            </p>
                        </div>
                        
                        <div>
                            <h6><i class="fas fa-share-alt text-primary me-2"></i> Media Sosial</h6>
                            <div class="d-flex gap-2">
                                <a href="#" class="btn btn-outline-primary btn-sm">
                                    <i class="fab fa-facebook"></i>
                                </a>
                                <a href="#" class="btn btn-outline-primary btn-sm">
                                    <i class="fab fa-instagram"></i>
                                </a>
                                <a href="https://wa.me/6281317975623" class="btn btn-outline-success btn-sm" target="_blank">
                                    <i class="fab fa-whatsapp"></i>
                                </a>
                                <a href="#" class="btn btn-outline-info btn-sm">
                                    <i class="fab fa-twitter"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- FAQ -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h6 class="mb-0">Pertanyaan Umum</h6>
                    </div>
                    <div class="card-body">
                        <div class="accordion" id="faqAccordion">
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="faq1">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse1">
                                        Bagaimana cara memesan?
                                    </button>
                                </h2>
                                <div id="collapse1" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body">
                                        Anda dapat memesan melalui website dengan mendaftar akun terlebih dahulu, lalu pilih produk dan lakukan checkout.
                                    </div>
                                </div>
                            </div>
                            
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="faq2">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse2">
                                        Berapa lama pengiriman?
                                    </button>
                                </h2>
                                <div id="collapse2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body">
                                        Pengiriman biasanya memakan waktu 2-3 hari kerja untuk wilayah Sumatera Utara dan 3-5 hari kerja untuk wilayah lainnya.
                                    </div>
                                </div>
                            </div>
                            
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="faq3">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse3">
                                        Apakah bisa custom ulos?
                                    </button>
                                </h2>
                                <div id="collapse3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body">
                                        Ya, kami menerima pesanan custom ulos dengan motif dan ukuran sesuai kebutuhan. Silakan hubungi kami untuk konsultasi.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>



<?php include 'includes/footer.php'; ?>