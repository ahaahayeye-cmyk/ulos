<?php
require_once 'includes/config.php';

$page_title = "Tentang Kami";
include 'includes/header.php';
?>

<!-- Page Header -->
<div class="container">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="pt-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Beranda</a></li>
            <li class="breadcrumb-item active">Tentang Kami</li>
        </ol>
    </nav>
    
    <!-- Page Title -->
    <div class="page-header text-center py-4">
        <h1 class="page-title">Tentang Gerai Tano Batak</h1>
        <p class="page-subtitle">Melestarikan budaya Batak melalui ulos berkualitas tinggi</p>
    </div>
</div>

<!-- About Content -->
<section class="py-4">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="about-content-card">
                    <h2 class="content-title">Sejarah Kami</h2>
                    <p class="content-text">Gerai Tano Batak didirikan dengan misi untuk melestarikan dan mempromosikan budaya Batak melalui ulos berkualitas tinggi. Kami memahami bahwa ulos bukan hanya sekadar kain, tetapi merupakan warisan budaya yang sarat dengan makna dan filosofi.</p>
                    
                    <p class="content-text">Sejak didirikan, kami telah berkomitmen untuk menyediakan ulos autentik yang dibuat oleh pengrajin lokal dengan teknik tradisional yang telah diwariskan turun-temurun. Setiap ulos yang kami jual memiliki cerita dan makna yang mendalam.</p>
                    
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <h3 class="content-subtitle">Visi Kami</h3>
                            <p class="content-text">Menjadi platform terdepan dalam melestarikan dan mempromosikan budaya ulos Batak ke seluruh Indonesia dan dunia.</p>
                        </div>
                        <div class="col-md-6">
                            <h3 class="content-subtitle">Misi Kami</h3>
                            <ul class="content-list">
                                <li>Menyediakan ulos berkualitas tinggi dengan harga yang terjangkau</li>
                                <li>Mendukung pengrajin lokal dan ekonomi kreatif</li>
                                <li>Mengedukasi masyarakat tentang makna dan filosofi ulos</li>
                                <li>Melestarikan teknik pembuatan ulos tradisional</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Values Section -->
<section class="py-4 bg-light">
    <div class="container">
        <h2 class="section-title text-center mb-4">Nilai-Nilai Kami</h2>
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="text-center">
                    <div class="mb-3">
                        <i class="fas fa-heart fa-3x text-primary"></i>
                    </div>
                    <h5>Kualitas</h5>
                    <p>Kami hanya menyediakan ulos dengan kualitas terbaik yang dibuat oleh pengrajin berpengalaman.</p>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="text-center">
                    <div class="mb-3">
                        <i class="fas fa-users fa-3x text-primary"></i>
                    </div>
                    <h5>Komunitas</h5>
                    <p>Kami mendukung komunitas pengrajin lokal dan membantu mereka mengembangkan usaha.</p>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="text-center">
                    <div class="mb-3">
                        <i class="fas fa-leaf fa-3x text-primary"></i>
                    </div>
                    <h5>Tradisi</h5>
                    <p>Kami berkomitmen untuk melestarikan tradisi dan budaya pembuatan ulos yang autentik.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Team Section -->
<section class="py-5">
    <div class="container">
        <h2 class="text-center mb-5">Tim Kami</h2>
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="card text-center">
                    <div class="card-body">
                        <div class="mb-3">
                            <div class="bg-primary rounded-circle d-inline-flex align-items-center justify-content-center" 
                                 style="width: 80px; height: 80px;">
                                <i class="fas fa-user fa-2x text-white"></i>
                            </div>
                        </div>
                        <h5>Bapak Sihombing</h5>
                        <p class="text-muted">Founder & CEO</p>
                        <p class="small">Pengusaha yang berpengalaman dalam bidang tekstil tradisional dengan visi melestarikan budaya Batak.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card text-center">
                    <div class="card-body">
                        <div class="mb-3">
                            <div class="bg-primary rounded-circle d-inline-flex align-items-center justify-content-center" 
                                 style="width: 80px; height: 80px;">
                                <i class="fas fa-user fa-2x text-white"></i>
                            </div>
                        </div>
                        <h5>Ibu Situmorang</h5>
                        <p class="text-muted">Head of Production</p>
                        <p class="small">Ahli dalam teknik pembuatan ulos tradisional dengan pengalaman lebih dari 20 tahun.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card text-center">
                    <div class="card-body">
                        <div class="mb-3">
                            <div class="bg-primary rounded-circle d-inline-flex align-items-center justify-content-center" 
                                 style="width: 80px; height: 80px;">
                                <i class="fas fa-user fa-2x text-white"></i>
                            </div>
                        </div>
                        <h5>Bapak Manurung</h5>
                        <p class="text-muted">Marketing Manager</p>
                        <p class="small">Spesialis dalam pemasaran produk budaya dengan fokus pada digitalisasi dan e-commerce.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Philosophy Section -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto text-center">
                <h2 class="mb-4">Filosofi Ulos</h2>
                <p class="lead">Ulos dalam budaya Batak memiliki makna yang sangat mendalam. Kata "ulos" berasal dari bahasa Batak yang berarti "memberi kehangatan". Ulos tidak hanya berfungsi sebagai pakaian, tetapi juga sebagai simbol kasih sayang, perlindungan, dan berkah.</p>
                
                <div class="row mt-5">
                    <div class="col-md-4 mb-3">
                        <h5>Pemberian</h5>
                        <p>Ulos diberikan sebagai tanda kasih sayang dan penghormatan dalam berbagai upacara adat.</p>
                    </div>
                    <div class="col-md-4 mb-3">
                        <h5>Perlindungan</h5>
                        <p>Dipercaya memberikan perlindungan dan berkah bagi yang memakainya.</p>
                    </div>
                    <div class="col-md-4 mb-3">
                        <h5>Identitas</h5>
                        <p>Menjadi identitas budaya yang membedakan setiap marga dan daerah.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="py-5">
    <div class="container text-center">
        <h2 class="mb-4">Bergabunglah dengan Kami</h2>
        <p class="lead mb-4">Mari bersama-sama melestarikan budaya ulos dan mendukung pengrajin lokal</p>
        <div class="row justify-content-center">
            <div class="col-md-6">
                <a href="products.php" class="btn btn-primary btn-lg me-3">
                    <i class="fas fa-shopping-bag"></i> Lihat Produk
                </a>
                <a href="contact.php" class="btn btn-outline-primary btn-lg">
                    <i class="fas fa-envelope"></i> Hubungi Kami
                </a>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>