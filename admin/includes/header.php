<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>Admin Panel - Gerai Tano Batak</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    <style>
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        }
        .sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 0.75rem 1rem;
            border-radius: 8px;
            margin: 2px 8px;
            transition: all 0.3s ease;
        }
        .sidebar .nav-link:hover {
            color: #fff;
            background-color: rgba(255,255,255,0.1);
            transform: translateX(5px);
        }
        .sidebar .nav-link.active {
            color: #fff;
            background-color: rgba(255,255,255,0.2);
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
        }
        .sidebar .nav-link i {
            width: 20px;
            text-align: center;
        }
        .admin-brand {
            background: rgba(255,255,255,0.1);
            border-radius: 10px;
            margin: 10px;
            padding: 15px;
            text-align: center;
        }
        .admin-brand h5 {
            margin: 0;
            color: #fff;
            font-weight: 600;
        }
        .admin-brand small {
            color: rgba(255,255,255,0.7);
        }
        .main-content {
            margin-left: 250px;
            padding: 20px;
            background-color: #f8f9fa;
            min-height: 100vh;
            padding-top: 30px;
        }
        .top-navbar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            height: 70px;
            z-index: 1000;
        }
        .navbar-brand-admin {
            font-weight: 600;
            color: #fff !important;
        }
        .stats-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
        }
        /* Perbaikan untuk mencegah tumpang tindih */
        .page-header {
            margin-bottom: 25px;
            padding-top: 10px;
        }
        
        .btn-add-product {
            margin-top: 10px;
        }
        
        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                margin-top: 70px !important;
                padding-top: 20px;
            }
            .sidebar {
                position: fixed;
                top: 0;
                left: -250px;
                width: 250px;
                z-index: 1000;
                transition: left 0.3s;
            }
            .sidebar.show {
                left: 0;
            }
            .top-navbar {
                margin-left: 0 !important;
                width: 100% !important;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <nav class="sidebar position-fixed top-0 start-0" style="width: 250px;">
        <div class="admin-brand">
            <i class="fas fa-crown fa-2x mb-2" style="color: #ffd700;"></i>
            <h5>Admin Panel</h5>
            <small>Gerai Tano Batak</small>
        </div>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>" href="index.php">
                    <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'products.php' ? 'active' : ''; ?>" href="products.php">
                    <i class="fas fa-box me-2"></i> Produk
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'categories.php' ? 'active' : ''; ?>" href="categories.php">
                    <i class="fas fa-tags me-2"></i> Kategori
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'orders.php' ? 'active' : ''; ?>" href="orders.php">
                    <i class="fas fa-shopping-cart me-2"></i> Pesanan
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'customers.php' ? 'active' : ''; ?>" href="customers.php">
                    <i class="fas fa-users me-2"></i> Customer
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'reports.php' ? 'active' : ''; ?>" href="reports.php">
                    <i class="fas fa-chart-bar me-2"></i> Laporan
                </a>
            </li>
            <li class="nav-item">
                <hr class="text-muted">
            </li>
            <li class="nav-item">
                <a class="nav-link" href="../index.php" target="_blank">
                    <i class="fas fa-external-link-alt me-2"></i> Lihat Website
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="../logout.php">
                    <i class="fas fa-sign-out-alt me-2"></i> Logout
                </a>
            </li>
        </ul>
    </nav>

    <!-- Top Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark top-navbar position-fixed w-100" style="z-index: 1001; margin-left: 250px; width: calc(100% - 250px) !important; top: 0;">
        <div class="container-fluid">
            <button class="navbar-toggler d-lg-none" type="button" onclick="toggleSidebar()">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <a class="navbar-brand navbar-brand-admin d-none d-lg-block" href="index.php">
                <i class="fas fa-tachometer-alt me-2"></i>
                <?php echo isset($page_title) ? $page_title : 'Dashboard'; ?>
            </a>
            
            <div class="navbar-nav ms-auto">
                <div class="nav-item me-3">
                    <span class="navbar-text">
                        <i class="fas fa-clock me-1"></i>
                        <span id="current-time"></span>
                    </span>
                </div>
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-user-circle me-1"></i> 
                        <?php echo isset($_SESSION['full_name']) ? $_SESSION['full_name'] : $_SESSION['username']; ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="../profile.php"><i class="fas fa-user me-2"></i>Profil</a></li>
                        <li><a class="dropdown-item" href="../index.php" target="_blank"><i class="fas fa-external-link-alt me-2"></i>Lihat Website</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="../logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>
    
    <script>
    // Update waktu real-time
    function updateTime() {
        const now = new Date();
        const timeString = now.toLocaleTimeString('id-ID');
        const dateString = now.toLocaleDateString('id-ID', {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
        document.getElementById('current-time').innerHTML = timeString + '<br><small>' + dateString + '</small>';
    }
    
    // Update setiap detik
    setInterval(updateTime, 1000);
    updateTime(); // Panggil sekali saat load
    
    // Toggle sidebar untuk mobile
    function toggleSidebar() {
        document.querySelector('.sidebar').classList.toggle('show');
    }
    </script>

    <!-- Main Content -->
    <main class="main-content" style="margin-top: 90px;">
        <div class="container-fluid">