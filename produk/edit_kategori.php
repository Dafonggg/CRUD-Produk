<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include 'koneksi.php';
$pesan = '';
$kategori_data = null;

if (!isset($_GET['id'])) {
    header("Location: kategori.php?pesan=id_tidak_ditemukan");
    exit;
}

$id_kategori_edit = mysqli_real_escape_string($koneksi, $_GET['id']);

// Logika untuk memproses update kategori
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_kategori'])) {
    $jenis_kategori_update = mysqli_real_escape_string($koneksi, trim($_POST['jenis_kategori']));
    $id_kategori_lama = mysqli_real_escape_string($koneksi, $_POST['id_kategori_lama']); // ID asli untuk klausa WHERE
    $panjang_maksimum_jenis = 100; // Sesuaikan dengan batas kolom 'jenis' di DB Anda

    if (empty($jenis_kategori_update)) {
        $pesan = "<div class='alert alert-danger alert-dismissible fade show' role='alert'>
                    Jenis Kategori tidak boleh kosong.
                    <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                  </div>";
    } elseif (strlen($jenis_kategori_update) > $panjang_maksimum_jenis) {
        $pesan = "<div class='alert alert-danger alert-dismissible fade show' role='alert'>
                    Nama Kategori terlalu panjang. Maksimal {$panjang_maksimum_jenis} karakter.
                    <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                  </div>";
    } else {
        // ID kategori tidak diubah di sini, hanya jenisnya.
        // Jika Anda ingin mengizinkan perubahan ID Kategori, logikanya akan lebih kompleks
        // dan perlu hati-hati terkait relasi dengan tabel produk.
        $update_query = "UPDATE kategori SET jenis = '$jenis_kategori_update' WHERE id_kategori = '$id_kategori_lama'";

        if (mysqli_query($koneksi, $update_query)) {
            // Redirect kembali ke kategori.php dengan pesan sukses
            header("Location: kategori.php?pesan=update_sukses");
            exit;
        } else {
            // Tetap di halaman edit dengan pesan error
            $pesan = "<div class='alert alert-danger alert-dismissible fade show' role='alert'>
                        <strong>Gagal!</strong> Error: " . mysqli_error($koneksi) . "
                        <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                      </div>";
        }
    }
}

// Ambil data kategori yang akan diedit
$query_data = mysqli_query($koneksi, "SELECT * FROM kategori WHERE id_kategori = '$id_kategori_edit'");
if ($query_data && mysqli_num_rows($query_data) > 0) {
    $kategori_data = mysqli_fetch_assoc($query_data);
} else {
    // Jika ID tidak ditemukan setelah pengecekan awal (misal dihapus user lain)
    header("Location: kategori.php?pesan=data_tidak_ditemukan");
    exit;
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Kategori - Kue Balok Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="d-flex flex-column min-vh-100">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php"><i class="fas fa-birthday-cake"></i> Kue Balok Admin</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php"><i class="fas fa-home"></i> Beranda</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="input.php"><i class="fas fa-plus-circle"></i> Tambah Produk</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" aria-current="page" href="kategori.php"><i class="fas fa-tags"></i> Kategori</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4" id="main-content">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="d-flex justify-content-between align-items-center">
                    <h1 class="page-title mb-4 text-start">Edit Data Kategori</h1>
                    <a href="kategori.php" class="btn btn-outline-secondary mb-4"><i class="fas fa-arrow-left"></i> Kembali ke Daftar Kategori</a>
                </div>

                <?php echo $pesan; ?>

                <?php if ($kategori_data): ?>
                <div class="card">
                    <div class="card-header custom-card-header">
                        <i class="fas fa-edit"></i> Mengubah Kategori: <?php echo htmlspecialchars($kategori_data['jenis']); ?>
                    </div>
                    <div class="card-body">
                        <form action="edit_kategori.php?id=<?php echo urlencode($id_kategori_edit); ?>" method="post">
                            <input type="hidden" name="id_kategori_lama" value="<?php echo htmlspecialchars($kategori_data['id_kategori']); ?>">

                            <div class="mb-3">
                                <label for="id_kategori_display" class="form-label">ID Kategori</label>
                                <input type="text" class="form-control" id="id_kategori_display" value="<?php echo htmlspecialchars($kategori_data['id_kategori']); ?>" readonly disabled>
                                <small class="form-text text-muted">ID Kategori tidak dapat diubah melalui form ini.</small>
                            </div>

                            <div class="mb-3">
                                <label for="jenis_kategori" class="form-label">Jenis/Nama Kategori <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="jenis_kategori" id="jenis_kategori" value="<?php echo htmlspecialchars($kategori_data['jenis']); ?>" required>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" name="update_kategori" class="btn btn-primary"><i class="fas fa-save"></i> Simpan Perubahan</button>
                            </div>
                        </form>
                    </div>
                </div>
                <?php else: ?>
                    <div class="alert alert-warning">Data kategori tidak ditemukan.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <footer class="bg-dark text-white text-center p-3 mt-auto">
        <p class="mb-0">&copy; <?php echo date("Y"); ?> Kue Balok Admin. All Rights Reserved.</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>