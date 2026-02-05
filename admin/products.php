<?php
require_once '../includes/config.php';

// Cek apakah user adalah admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

$error = '';
$success = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Add product
    if (isset($_POST['name']) && isset($_POST['description']) && isset($_POST['price']) && isset($_POST['stock']) && isset($_POST['category_id']) && !isset($_POST['id'])) {
        $name = clean_input($_POST['name']);
        $description = clean_input($_POST['description']);
        $price = (float)$_POST['price'];
        $stock = (int)$_POST['stock'];
        $category_id = (int)$_POST['category_id'];
        
        if (empty($name) || empty($description) || $price <= 0 || $stock < 0 || $category_id <= 0) {
            $error = 'Semua field harus diisi dengan benar';
        } else {
            try {
                $image = '';
                // Handle file upload
                if (isset($_FILES['image'])) {
                    $upload_error = $_FILES['image']['error'];
                    
                    if ($upload_error == UPLOAD_ERR_OK) {
                        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                        $filename = $_FILES['image']['name'];
                        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                        $file_size = $_FILES['image']['size'];
                        $max_size = 5 * 1024 * 1024; // 5MB
                        
                        if (empty($filename)) {
                            // File tidak dipilih, lanjutkan tanpa gambar
                        } elseif (!in_array($ext, $allowed)) {
                            $error = 'Format file tidak didukung. Gunakan JPG, JPEG, PNG, GIF, atau WEBP';
                        } elseif ($file_size > $max_size) {
                            $error = 'Ukuran file terlalu besar. Maksimal 5MB';
                        } else {
                            // Buat nama file unik
                            $image = time() . '_' . uniqid() . '.' . $ext;
                            
                            // Upload file
                            $upload_dir = '../uploads/';
                            if (!is_dir($upload_dir)) {
                                mkdir($upload_dir, 0755, true);
                            }
                            
                            $target_path = $upload_dir . $image;
                            if (!move_uploaded_file($_FILES['image']['tmp_name'], $target_path)) {
                                // Fallback dengan copy
                                if (!copy($_FILES['image']['tmp_name'], $target_path)) {
                                    $error = 'Gagal mengupload gambar';
                                    $image = '';
                                }
                            }
                        }
                    } elseif ($upload_error != UPLOAD_ERR_NO_FILE) {
                        // Ada error upload selain tidak ada file
                        switch ($upload_error) {
                            case UPLOAD_ERR_INI_SIZE:
                                $error = 'File terlalu besar (melebihi upload_max_filesize)';
                                break;
                            case UPLOAD_ERR_FORM_SIZE:
                                $error = 'File terlalu besar (melebihi MAX_FILE_SIZE)';
                                break;
                            case UPLOAD_ERR_PARTIAL:
                                $error = 'File hanya terupload sebagian';
                                break;
                            case UPLOAD_ERR_NO_TMP_DIR:
                                $error = 'Direktori temporary tidak ada';
                                break;
                            case UPLOAD_ERR_CANT_WRITE:
                                $error = 'Gagal menulis file ke disk';
                                break;
                            case UPLOAD_ERR_EXTENSION:
                                $error = 'Upload dihentikan oleh ekstensi PHP';
                                break;
                            default:
                                $error = 'Error upload tidak dikenal: ' . $upload_error;
                        }
                    }
                }
                
                // Insert ke database
                $stmt = $pdo->prepare("INSERT INTO products (name, description, price, stock, category_id, image) VALUES (?, ?, ?, ?, ?, ?)");
                $result = $stmt->execute([$name, $description, $price, $stock, $category_id, $image]);
                
                if ($result) {
                    $product_id = $pdo->lastInsertId();
                    
                    // Handle multiple images upload
                    if (isset($_FILES['gallery_images']) && !empty($_FILES['gallery_images']['name'][0])) {
                        $upload_dir = '../uploads/';
                        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                        $max_size = 5 * 1024 * 1024; // 5MB
                        
                        foreach ($_FILES['gallery_images']['name'] as $key => $filename) {
                            if (!empty($filename)) {
                                $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                                $file_size = $_FILES['gallery_images']['size'][$key];
                                $tmp_name = $_FILES['gallery_images']['tmp_name'][$key];
                                
                                if (in_array($ext, $allowed) && $file_size <= $max_size) {
                                    $gallery_image = time() . '_' . uniqid() . '_' . $key . '.' . $ext;
                                    $target_path = $upload_dir . $gallery_image;
                                    
                                    if (move_uploaded_file($tmp_name, $target_path) || copy($tmp_name, $target_path)) {
                                        // Set first gallery image as primary if no main image
                                        $is_primary = ($key == 0 && empty($image)) ? 1 : 0;
                                        $alt_text = $name . ' - Gambar ' . ($key + 1);
                                        
                                        add_product_image($pdo, $product_id, $gallery_image, $is_primary, $alt_text);
                                    }
                                }
                            }
                        }
                    }
                    
                    $success = 'Produk berhasil ditambahkan';
                } else {
                    $error = 'Gagal menyimpan produk ke database';
                }
            } catch (Exception $e) {
                $error = 'Terjadi kesalahan: ' . $e->getMessage();
            }
        }
    // Update product
    } elseif (isset($_POST['id']) && isset($_POST['name']) && isset($_POST['description'])) {
        $id = (int)$_POST['id'];
        $name = clean_input($_POST['name']);
        $description = clean_input($_POST['description']);
        $price = (float)$_POST['price'];
        $stock = (int)$_POST['stock'];
        $category_id = (int)$_POST['category_id'];
        $status = clean_input($_POST['status']);
        
        if (empty($name) || empty($description) || $price <= 0 || $stock < 0 || $category_id <= 0) {
            $error = 'Semua field harus diisi dengan benar';
        } else {
            try {
                // Handle file upload
                $update_image = false;
                $new_image = '';
                if (isset($_FILES['image'])) {
                    $upload_error = $_FILES['image']['error'];
                    
                    if ($upload_error == UPLOAD_ERR_OK) {
                        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                        $filename = $_FILES['image']['name'];
                        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                        $file_size = $_FILES['image']['size'];
                        $max_size = 5 * 1024 * 1024; // 5MB
                        
                        if (empty($filename)) {
                            // File tidak dipilih, tidak update gambar
                        } elseif (!in_array($ext, $allowed)) {
                            $error = 'Format file tidak didukung. Gunakan JPG, JPEG, PNG, GIF, atau WEBP';
                        } elseif ($file_size > $max_size) {
                            $error = 'Ukuran file terlalu besar. Maksimal 5MB';
                        } else {
                            // Buat nama file unik
                            $new_image = time() . '_' . uniqid() . '.' . $ext;
                            
                            // Pastikan folder uploads ada dan dapat ditulis
                            $upload_dir = '../uploads/';
                            if (!is_dir($upload_dir)) {
                                if (!mkdir($upload_dir, 0755, true)) {
                                    $error = 'Gagal membuat direktori upload';
                                }
                            }
                            
                            if (empty($error)) {
                                $target_path = $upload_dir . $new_image;
                                if (move_uploaded_file($_FILES['image']['tmp_name'], $target_path) || copy($_FILES['image']['tmp_name'], $target_path)) {
                                    $update_image = true;
                                    
                                    // Hapus gambar lama jika ada
                                    $stmt_old = $pdo->prepare("SELECT image FROM products WHERE id = ?");
                                    $stmt_old->execute([$id]);
                                    $old_product = $stmt_old->fetch();
                                    
                                    if ($old_product && $old_product['image'] && file_exists('../uploads/' . $old_product['image'])) {
                                        // Jangan hapus file default
                                        if (!in_array($old_product['image'], ['ulos1.svg', 'ulos2.svg', 'ulos3.svg', 'ulos4.svg', 'ulos5.svg'])) {
                                            unlink('../uploads/' . $old_product['image']);
                                        }
                                    }
                                } else {
                                    $error = 'Gagal mengupload gambar';
                                }
                            }
                        }
                    } elseif ($upload_error != UPLOAD_ERR_NO_FILE) {
                        // Ada error upload selain tidak ada file
                        switch ($upload_error) {
                            case UPLOAD_ERR_INI_SIZE:
                                $error = 'File terlalu besar (melebihi upload_max_filesize)';
                                break;
                            case UPLOAD_ERR_FORM_SIZE:
                                $error = 'File terlalu besar (melebihi MAX_FILE_SIZE)';
                                break;
                            case UPLOAD_ERR_PARTIAL:
                                $error = 'File hanya terupload sebagian';
                                break;
                            case UPLOAD_ERR_NO_TMP_DIR:
                                $error = 'Direktori temporary tidak ada';
                                break;
                            case UPLOAD_ERR_CANT_WRITE:
                                $error = 'Gagal menulis file ke disk';
                                break;
                            case UPLOAD_ERR_EXTENSION:
                                $error = 'Upload dihentikan oleh ekstensi PHP';
                                break;
                            default:
                                $error = 'Error upload tidak dikenal: ' . $upload_error;
                        }
                    }
                }
                
                if ($update_image) {
                    $sql = "UPDATE products SET name = ?, description = ?, price = ?, stock = ?, category_id = ?, status = ?, image = ? WHERE id = ?";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$name, $description, $price, $stock, $category_id, $status, $new_image, $id]);
                } else {
                    $sql = "UPDATE products SET name = ?, description = ?, price = ?, stock = ?, category_id = ?, status = ? WHERE id = ?";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$name, $description, $price, $stock, $category_id, $status, $id]);
                }
                
                // Handle additional gallery images upload
                if (isset($_FILES['gallery_images']) && !empty($_FILES['gallery_images']['name'][0])) {
                    $upload_dir = '../uploads/';
                    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                    $max_size = 5 * 1024 * 1024; // 5MB
                    
                    foreach ($_FILES['gallery_images']['name'] as $key => $filename) {
                        if (!empty($filename)) {
                            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                            $file_size = $_FILES['gallery_images']['size'][$key];
                            $tmp_name = $_FILES['gallery_images']['tmp_name'][$key];
                            
                            if (in_array($ext, $allowed) && $file_size <= $max_size) {
                                $gallery_image = time() . '_' . uniqid() . '_' . $key . '.' . $ext;
                                $target_path = $upload_dir . $gallery_image;
                                
                                if (move_uploaded_file($tmp_name, $target_path) || copy($tmp_name, $target_path)) {
                                    $alt_text = $name . ' - Gambar tambahan ' . ($key + 1);
                                    add_product_image($pdo, $id, $gallery_image, false, $alt_text);
                                }
                            }
                        }
                    }
                }
                
                $success = 'Produk berhasil diupdate';
            } catch (Exception $e) {
                $error = 'Terjadi kesalahan: ' . $e->getMessage();
            }
        }
    // Delete product
    } elseif (isset($_POST['id']) && !isset($_POST['name'])) {
        $id = (int)$_POST['id'];
        try {
            $stmt = $pdo->prepare("UPDATE products SET status = 'inactive' WHERE id = ?");
            $stmt->execute([$id]);
            $success = 'Produk berhasil dihapus';
        } catch (Exception $e) {
            $error = 'Terjadi kesalahan: ' . $e->getMessage();
        }
    }
}

// Get products
$search = isset($_GET['search']) ? clean_input($_GET['search']) : '';
$category_filter = isset($_GET['category']) ? (int)$_GET['category'] : 0;

$where_conditions = [];
$params = [];

if ($search) {
    $where_conditions[] = "(p.name LIKE ? OR p.description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($category_filter) {
    $where_conditions[] = "p.category_id = ?";
    $params[] = $category_filter;
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

$sql = "SELECT p.*, c.name as category_name,
               COALESCE(AVG(pr.rating), 0) as avg_rating,
               COUNT(pr.id) as review_count
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        LEFT JOIN product_reviews pr ON p.id = pr.product_id
        $where_clause 
        GROUP BY p.id
        ORDER BY p.created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

// Get categories for dropdown
$stmt = $pdo->prepare("SELECT * FROM categories ORDER BY name");
$stmt->execute();
$categories = $stmt->fetchAll();

$page_title = "Kelola Produk";
include 'includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4 page-header">
    <h2>Kelola Produk</h2>
    <button class="btn btn-primary btn-add-product" data-bs-toggle="modal" data-bs-target="#addProductModal">
        <i class="fas fa-plus"></i> Tambah Produk
    </button>
</div>

<?php if ($error): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<?php if ($success): ?>
    <div class="alert alert-success"><?php echo $success; ?></div>
<?php endif; ?>

<!-- Filter -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-4">
                <input type="text" class="form-control" name="search" placeholder="Cari produk..." 
                       value="<?php echo htmlspecialchars($search); ?>">
            </div>
            <div class="col-md-3">
                <select class="form-select" name="category">
                    <option value="">Semua Kategori</option>
                    <?php foreach($categories as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>" 
                                <?php echo $category_filter == $cat['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-outline-primary">Filter</button>
            </div>
            <div class="col-md-2">
                <a href="products.php" class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<!-- Products Table -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Gambar</th>
                        <th>Nama Produk</th>
                        <th>Kategori</th>
                        <th>Harga</th>
                        <th>Stok</th>
                        <th>Rating</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($products as $product): ?>
                    <tr>
                        <td>
                            <img src="<?php echo $product['image'] ? '../uploads/' . $product['image'] : '../assets/images/no-image.svg'; ?>" 
                                 class="img-thumbnail" style="width: 60px; height: 60px; object-fit: cover;" 
                                 alt="<?php echo htmlspecialchars($product['name']); ?>"
                                 onerror="this.src='../assets/images/no-image.svg'">
                        </td>
                        <td>
                            <strong><?php echo htmlspecialchars($product['name']); ?></strong>
                            <br>
                            <small class="text-muted"><?php echo htmlspecialchars(substr($product['description'], 0, 50)) . '...'; ?></small>
                        </td>
                        <td><?php echo htmlspecialchars($product['category_name']); ?></td>
                        <td><?php echo format_rupiah($product['price']); ?></td>
                        <td>
                            <span class="badge <?php echo $product['stock'] <= 5 ? 'bg-danger' : 'bg-success'; ?>">
                                <?php echo $product['stock']; ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($product['review_count'] > 0): ?>
                                <div class="d-flex align-items-center">
                                    <?php echo display_rating($product['avg_rating'], false); ?>
                                    <small class="text-muted ms-2">
                                        (<?php echo $product['review_count']; ?>)
                                    </small>
                                </div>
                            <?php else: ?>
                                <small class="text-muted">Belum ada review</small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge <?php echo $product['status'] == 'active' ? 'bg-success' : 'bg-secondary'; ?>">
                                <?php echo ucfirst($product['status']); ?>
                            </span>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary" onclick="editProduct(<?php echo htmlspecialchars(json_encode($product)); ?>)">
                                <i class="fas fa-edit"></i>
                            </button>
                            <form method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus produk ini?')">
                                <input type="hidden" name="id" value="<?php echo $product['id']; ?>">
                                <button type="submit" name="delete_product" class="btn btn-sm btn-outline-danger">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Product Modal -->
<div class="modal fade" id="addProductModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Produk</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="MAX_FILE_SIZE" value="5242880"> <!-- 5MB -->
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">Nama Produk *</label>
                            <input type="text" class="form-control" name="name" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="category_id" class="form-label">Kategori *</label>
                            <select class="form-select" name="category_id" required>
                                <option value="">Pilih Kategori</option>
                                <?php foreach($categories as $cat): ?>
                                    <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Deskripsi *</label>
                        <textarea class="form-control" name="description" rows="4" required></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="price" class="form-label">Harga *</label>
                            <input type="number" class="form-control" name="price" min="0" step="0.01" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="stock" class="form-label">Stok *</label>
                            <input type="number" class="form-control" name="stock" min="0" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="image" class="form-label">Gambar Utama</label>
                        <input type="file" class="form-control" name="image" accept="image/*" onchange="previewImage(this, 'add-preview')">
                        <small class="text-muted">Format: JPG, JPEG, PNG, GIF, WEBP. Maksimal 5MB.</small>
                        <div id="add-preview" class="mt-2"></div>
                    </div>
                    <div class="mb-3">
                        <label for="gallery_images" class="form-label">Gallery Gambar (Multiple)</label>
                        <input type="file" class="form-control" name="gallery_images[]" accept="image/*" multiple onchange="previewGalleryImages(this, 'add-gallery-preview')">
                        <small class="text-muted">Pilih beberapa gambar sekaligus. Format: JPG, JPEG, PNG, GIF, WEBP. Maksimal 5MB per file.</small>
                        <div id="add-gallery-preview" class="mt-2"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="add_product" class="btn btn-primary">Tambah Produk</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Product Modal -->
<div class="modal fade" id="editProductModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Produk</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="MAX_FILE_SIZE" value="5242880"> <!-- 5MB -->
                <input type="hidden" name="id" id="edit_id">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_name" class="form-label">Nama Produk *</label>
                            <input type="text" class="form-control" name="name" id="edit_name" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_category_id" class="form-label">Kategori *</label>
                            <select class="form-select" name="category_id" id="edit_category_id" required>
                                <option value="">Pilih Kategori</option>
                                <?php foreach($categories as $cat): ?>
                                    <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="edit_description" class="form-label">Deskripsi *</label>
                        <textarea class="form-control" name="description" id="edit_description" rows="4" required></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="edit_price" class="form-label">Harga *</label>
                            <input type="number" class="form-control" name="price" id="edit_price" min="0" step="0.01" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="edit_stock" class="form-label">Stok *</label>
                            <input type="number" class="form-control" name="stock" id="edit_stock" min="0" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="edit_status" class="form-label">Status *</label>
                            <select class="form-select" name="status" id="edit_status" required>
                                <option value="active">Aktif</option>
                                <option value="inactive">Tidak Aktif</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="edit_image" class="form-label">Gambar Utama</label>
                        <input type="file" class="form-control" name="image" accept="image/*" onchange="previewImage(this, 'edit-preview')">
                        <small class="text-muted">Kosongkan jika tidak ingin mengubah gambar. Format: JPG, PNG, GIF, WEBP.</small>
                        <div id="edit-preview" class="mt-2"></div>
                        <div id="current-image" class="mt-2"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Gallery Gambar Saat Ini</label>
                        <div id="current-gallery" class="mt-2"></div>
                        <label for="edit_gallery_images" class="form-label mt-3">Tambah Gambar Baru ke Gallery</label>
                        <input type="file" class="form-control" name="gallery_images[]" accept="image/*" multiple onchange="previewGalleryImages(this, 'edit-gallery-preview')">
                        <small class="text-muted">Pilih gambar baru untuk ditambahkan ke gallery. Format: JPG, JPEG, PNG, GIF, WEBP.</small>
                        <div id="edit-gallery-preview" class="mt-2"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="update_product" class="btn btn-primary">Update Produk</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editProduct(product) {
    document.getElementById('edit_id').value = product.id;
    document.getElementById('edit_name').value = product.name;
    document.getElementById('edit_description').value = product.description;
    document.getElementById('edit_price').value = product.price;
    document.getElementById('edit_stock').value = product.stock;
    document.getElementById('edit_category_id').value = product.category_id;
    document.getElementById('edit_status').value = product.status;
    
    // Show current image
    const currentImageDiv = document.getElementById('current-image');
    if (product.image) {
        currentImageDiv.innerHTML = `
            <label class="form-label">Gambar Utama Saat Ini:</label><br>
            <img src="../uploads/${product.image}" alt="Current Image" style="max-width: 200px; max-height: 150px; object-fit: cover; border-radius: 8px; border: 2px solid #dee2e6;">
        `;
    } else {
        currentImageDiv.innerHTML = '<small class="text-muted">Tidak ada gambar utama</small>';
    }
    
    // Load and show current gallery images
    loadProductGallery(product.id);
    
    // Clear previews
    document.getElementById('edit-preview').innerHTML = '';
    document.getElementById('edit-gallery-preview').innerHTML = '';
    
    new bootstrap.Modal(document.getElementById('editProductModal')).show();
}

function loadProductGallery(productId) {
    fetch(`get_product_gallery.php?product_id=${productId}`)
        .then(response => response.json())
        .then(data => {
            const galleryDiv = document.getElementById('current-gallery');
            
            if (data.success && data.images.length > 0) {
                let galleryHTML = '<div style="display: flex; flex-wrap: wrap; gap: 10px;">';
                
                data.images.forEach(image => {
                    galleryHTML += `
                        <div style="position: relative; border: 2px solid #dee2e6; border-radius: 8px; overflow: hidden;">
                            <img src="../uploads/${image.image_path}" alt="${image.alt_text}" 
                                 style="width: 100px; height: 100px; object-fit: cover;">
                            <div style="position: absolute; top: 5px; right: 5px;">
                                <button type="button" class="btn btn-danger btn-sm" 
                                        onclick="deleteProductImage(${image.id}, this)" 
                                        style="padding: 2px 6px; font-size: 10px;">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                            ${image.is_primary ? '<div style="position: absolute; bottom: 5px; left: 5px; background: #28a745; color: white; padding: 2px 6px; border-radius: 4px; font-size: 10px;">Primary</div>' : ''}
                        </div>
                    `;
                });
                
                galleryHTML += '</div>';
                galleryDiv.innerHTML = galleryHTML;
            } else {
                galleryDiv.innerHTML = '<small class="text-muted">Belum ada gambar gallery</small>';
            }
        })
        .catch(error => {
            console.error('Error loading gallery:', error);
            document.getElementById('current-gallery').innerHTML = '<small class="text-danger">Error loading gallery</small>';
        });
}

function deleteProductImage(imageId, button) {
    if (confirm('Yakin ingin menghapus gambar ini?')) {
        fetch('delete_product_image.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `image_id=${imageId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Remove the image container
                button.closest('div[style*="position: relative"]').remove();
            } else {
                alert(data.message || 'Gagal menghapus gambar');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan');
        });
    }
}

function previewImage(input, previewId) {
    const preview = document.getElementById(previewId);
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            preview.innerHTML = `
                <label class="form-label">Preview Gambar Baru:</label><br>
                <img src="${e.target.result}" alt="Preview" style="max-width: 200px; max-height: 150px; object-fit: cover; border-radius: 8px; border: 2px solid #28a745;">
            `;
        };
        
        reader.readAsDataURL(input.files[0]);
    } else {
        preview.innerHTML = '';
    }
}

function previewGalleryImages(input, previewId) {
    const preview = document.getElementById(previewId);
    preview.innerHTML = '';
    
    if (input.files && input.files.length > 0) {
        const label = document.createElement('label');
        label.className = 'form-label';
        label.textContent = `Preview Gallery (${input.files.length} gambar):`;
        preview.appendChild(label);
        
        const container = document.createElement('div');
        container.style.display = 'flex';
        container.style.flexWrap = 'wrap';
        container.style.gap = '10px';
        container.style.marginTop = '10px';
        
        Array.from(input.files).forEach((file, index) => {
            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    const imgContainer = document.createElement('div');
                    imgContainer.style.position = 'relative';
                    
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.alt = `Preview ${index + 1}`;
                    img.style.width = '100px';
                    img.style.height = '100px';
                    img.style.objectFit = 'cover';
                    img.style.borderRadius = '8px';
                    img.style.border = '2px solid #007bff';
                    
                    const badge = document.createElement('span');
                    badge.textContent = index + 1;
                    badge.style.position = 'absolute';
                    badge.style.top = '5px';
                    badge.style.right = '5px';
                    badge.style.background = '#007bff';
                    badge.style.color = 'white';
                    badge.style.borderRadius = '50%';
                    badge.style.width = '20px';
                    badge.style.height = '20px';
                    badge.style.display = 'flex';
                    badge.style.alignItems = 'center';
                    badge.style.justifyContent = 'center';
                    badge.style.fontSize = '12px';
                    
                    imgContainer.appendChild(img);
                    imgContainer.appendChild(badge);
                    container.appendChild(imgContainer);
                };
                
                reader.readAsDataURL(file);
            }
        });
        
        preview.appendChild(container);
    }
}

// Clear preview when modal is closed
document.getElementById('addProductModal').addEventListener('hidden.bs.modal', function() {
    document.getElementById('add-preview').innerHTML = '';
    document.getElementById('add-gallery-preview').innerHTML = '';
    const fileInputs = document.querySelectorAll('#addProductModal input[type="file"]');
    fileInputs.forEach(input => input.value = '');
});

document.getElementById('editProductModal').addEventListener('hidden.bs.modal', function() {
    document.getElementById('edit-preview').innerHTML = '';
    document.getElementById('edit-gallery-preview').innerHTML = '';
    const fileInputs = document.querySelectorAll('#editProductModal input[type="file"]');
    fileInputs.forEach(input => input.value = '');
});
</script>

<?php include 'includes/footer.php'; ?>