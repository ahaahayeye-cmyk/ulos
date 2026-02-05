<?php
require_once 'includes/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?redirect=" . urlencode($_SERVER['REQUEST_URI']));
    exit();
}

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Get user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update_profile'])) {
        $full_name = clean_input($_POST['full_name']);
        $email = clean_input($_POST['email']);
        $phone = clean_input($_POST['phone']);
        $address = clean_input($_POST['address']);
        
        if (empty($full_name) || empty($email)) {
            $error = 'Nama lengkap dan email harus diisi';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Format email tidak valid';
        } else {
            // Cek apakah email sudah digunakan user lain
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $stmt->execute([$email, $user_id]);
            
            if ($stmt->fetch()) {
                $error = 'Email sudah digunakan oleh user lain';
            } else {
                try {
                    $stmt = $pdo->prepare("UPDATE users SET full_name = ?, email = ?, phone = ?, address = ? WHERE id = ?");
                    $stmt->execute([$full_name, $email, $phone, $address, $user_id]);
                    
                    $success = 'Profil berhasil diupdate';
                    
                    // Update session data
                    $_SESSION['full_name'] = $full_name;
                    
                    // Refresh user data
                    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
                    $stmt->execute([$user_id]);
                    $user = $stmt->fetch();
                    
                } catch (Exception $e) {
                    $error = 'Terjadi kesalahan saat mengupdate profil';
                }
            }
        }
    } elseif (isset($_POST['change_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        if (empty($current_password) || empty($new_password)) {
            $error = 'Password lama dan password baru harus diisi';
        } elseif ($new_password !== $confirm_password) {
            $error = 'Password baru dan konfirmasi password tidak sama';
        } elseif (strlen($new_password) < 6) {
            $error = 'Password baru minimal 6 karakter';
        } elseif (!password_verify($current_password, $user['password'])) {
            $error = 'Password lama tidak benar';
        } else {
            try {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->execute([$hashed_password, $user_id]);
                
                $success = 'Password berhasil diubah';
                
            } catch (Exception $e) {
                $error = 'Terjadi kesalahan saat mengubah password';
            }
        }
    }
}

$page_title = "Profil Saya";
include 'includes/header.php';
?>

<!-- Breadcrumb -->
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="index.php">Beranda</a></li>
        <li class="breadcrumb-item active">Profil</li>
    </ol>
</nav>

<div class="row">
    <div class="col-lg-8">
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <!-- Update Profile -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-user"></i> Informasi Profil
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" class="needs-validation" novalidate>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" 
                                   value="<?php echo htmlspecialchars($user['username']); ?>" readonly>
                            <small class="text-muted">Username tidak dapat diubah</small>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email *</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?php echo htmlspecialchars($user['email']); ?>" required>
                            <div class="invalid-feedback">
                                Email harus diisi dengan format yang benar
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="full_name" class="form-label">Nama Lengkap *</label>
                        <input type="text" class="form-control" id="full_name" name="full_name" 
                               value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                        <div class="invalid-feedback">
                            Nama lengkap harus diisi
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="phone" class="form-label">No. Telepon</label>
                        <input type="tel" class="form-control" id="phone" name="phone" 
                               value="<?php echo htmlspecialchars($user['phone']); ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label for="address" class="form-label">Alamat</label>
                        <textarea class="form-control" id="address" name="address" rows="4"><?php echo htmlspecialchars($user['address']); ?></textarea>
                    </div>
                    
                    <button type="submit" name="update_profile" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Profil
                    </button>
                </form>
            </div>
        </div>
        
        <!-- Change Password -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-lock"></i> Ubah Password
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" class="needs-validation" novalidate>
                    <div class="mb-3">
                        <label for="current_password" class="form-label">Password Lama *</label>
                        <input type="password" class="form-control" id="current_password" name="current_password" required>
                        <div class="invalid-feedback">
                            Password lama harus diisi
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="new_password" class="form-label">Password Baru *</label>
                            <input type="password" class="form-control" id="new_password" name="new_password" 
                                   minlength="6" required>
                            <div class="invalid-feedback">
                                Password baru minimal 6 karakter
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="confirm_password" class="form-label">Konfirmasi Password Baru *</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                                   minlength="6" required>
                            <div class="invalid-feedback">
                                Konfirmasi password harus sama dengan password baru
                            </div>
                        </div>
                    </div>
                    
                    <button type="submit" name="change_password" class="btn btn-warning">
                        <i class="fas fa-key"></i> Ubah Password
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <!-- Account Info -->
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">Informasi Akun</h6>
            </div>
            <div class="card-body">
                <div class="text-center mb-3">
                    <div class="bg-primary rounded-circle d-inline-flex align-items-center justify-content-center" 
                         style="width: 80px; height: 80px;">
                        <i class="fas fa-user fa-2x text-white"></i>
                    </div>
                </div>
                
                <div class="text-center">
                    <h6><?php echo htmlspecialchars($user['full_name']); ?></h6>
                    <p class="text-muted"><?php echo htmlspecialchars($user['email']); ?></p>
                    <span class="badge bg-primary"><?php echo ucfirst($user['role']); ?></span>
                </div>
                
                <hr>
                
                <div class="small">
                    <p class="mb-1"><strong>Bergabung:</strong> <?php echo date('d/m/Y', strtotime($user['created_at'])); ?></p>
                    <p class="mb-0"><strong>Username:</strong> <?php echo htmlspecialchars($user['username']); ?></p>
                </div>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="card mt-3">
            <div class="card-body">
                <h6 class="card-title">Aksi Cepat</h6>
                <div class="d-grid gap-2">
                    <a href="orders.php" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-shopping-bag"></i> Lihat Pesanan
                    </a>
                    <a href="cart.php" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-shopping-cart"></i> Keranjang
                    </a>
                    <a href="products.php" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-store"></i> Belanja
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Validasi password match
document.getElementById('confirm_password').addEventListener('input', function() {
    const newPassword = document.getElementById('new_password').value;
    const confirmPassword = this.value;
    
    if (newPassword !== confirmPassword) {
        this.setCustomValidity('Password tidak sama');
    } else {
        this.setCustomValidity('');
    }
});
</script>

<?php include 'includes/footer.php'; ?>