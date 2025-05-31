<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Data Produk - Kue Balok Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="bg-light">

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php"><img src="img/logokecil30px.png" alt="LOGO KUE BALOK MANG WIRO" class="navbar-logo"></i> Kue Balok Admin</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                    aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
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
                        <a class="nav-link" href="kategori.php"><i class="fas fa-tags"></i> Kategori</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <?php
                include "koneksi.php";
                $id_produk = isset($_GET['id_produk']) ? mysqli_real_escape_string($koneksi, $_GET['id_produk']) : null;
                $product_data = null;

                if ($id_produk === null) {
                    echo "<div class='alert alert-danger text-center'>ID Produk tidak valid atau tidak ditemukan. <a href='index.php' class='alert-link'>Kembali ke Daftar</a></div>";
                } else {
                    $query_mysql = mysqli_query($koneksi, "SELECT * FROM produk WHERE id_produk='$id_produk'");
                    if ($query_mysql && mysqli_num_rows($query_mysql) > 0) {
                        $product_data = mysqli_fetch_array($query_mysql);
                    } else {
                        echo "<div class='alert alert-warning text-center'>Data produk dengan ID <strong>".htmlspecialchars($id_produk)."</strong> tidak ditemukan. <a href='index.php' class='alert-link'>Kembali ke Daftar</a></div>";
                    }
                }

                if ($product_data) {
                ?>
                <div class="d-flex justify-content-between align-items-center">
                    <h1 class="page-title mb-4 text-start">Edit Data Produk</h1>
                    <a href="index.php" class="btn btn-outline-secondary mb-4"><i class="fas fa-arrow-left"></i> Kembali ke Daftar</a>
                </div>

                <div class="card">
                    <div class="card-header custom-card-header">
                        <i class="fas fa-edit"></i> Mengubah Produk: <?php echo htmlspecialchars($product_data['varian']); ?>
                    </div>
                    <div class="card-body">
                        <form action="update.php" method="post" enctype="multipart/form-data">
                            <input type="hidden" name="id_produk" value="<?php echo htmlspecialchars($product_data['id_produk']); ?>">
                            <input type="hidden" name="foto_lama" value="<?php echo htmlspecialchars($product_data['foto']); // Send old photo name ?>">


                            <div class="mb-3">
                                <label for="id_kategori" class="form-label">Kategori <span class="text-danger">*</span></label>
                                <select name="id_kategori" id="id_kategori" class="form-select" required>
                                    <option value="" disabled>Pilih Kategori...</option>
                                    <?php
                                    $kategori_result = mysqli_query($koneksi, "SELECT id_kategori, jenis FROM kategori ORDER BY jenis ASC");
                                    if ($kategori_result) {
                                        while ($row = mysqli_fetch_assoc($kategori_result)) {
                                            $selected = ($row['id_kategori'] == $product_data['id_kategori']) ? "selected" : "";
                                            echo "<option value='".htmlspecialchars($row['id_kategori'])."' $selected>".htmlspecialchars($row['id_kategori'])." - ".htmlspecialchars($row['jenis'])."</option>";
                                        }
                                    }
                                    ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="varian" class="form-label">Varian Produk <span class="text-danger">*</span></label>
                                <input type="text" name="varian" id="varian" class="form-control" value="<?php echo htmlspecialchars($product_data['varian']); ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="foto" class="form-label">Ganti Foto Produk</label>
                                <input type="file" name="foto" id="foto" class="form-control" accept="image/png, image/jpeg, image/gif" onchange="previewImage(event)">
                                <small class="form-text text-muted">Kosongkan jika tidak ingin mengganti foto. Format: JPG, PNG, GIF. Maks: 5MB.</small>
                                <div class="mt-2">
                                    <p class="mb-1">Foto Saat Ini:</p>
                                    <?php if (!empty($product_data['foto']) && file_exists('foto/' . $product_data['foto'])): ?>
                                        <img id="current_foto" src="foto/<?php echo htmlspecialchars($product_data['foto']); ?>" alt="Foto Saat Ini" style="max-width: 150px; max-height: 150px; border:1px solid #ddd; padding:4px; border-radius:4px;">
                                    <?php else: ?>
                                        <p><em>Tidak ada foto.</em></p>
                                    <?php endif; ?>
                                </div>
                                <img id="preview" src="#" alt="Preview Foto Baru" style="display:none; margin-top:10px;" class="img-thumbnail"/>
                            </div>

                            <div class="mb-3">
                                <label for="stok" class="form-label">Stok <span class="text-danger">*</span></label>
                                <input type="number" name="stok" id="stok" class="form-control" value="<?php echo htmlspecialchars($product_data['stok']); ?>" min="0" required>
                            </div>

                            <div class="mb-3">
                                <label for="harga" class="form-label">Harga (Rp) <span class="text-danger">*</span></label>
                                <input type="number" name="harga" id="harga" class="form-control" value="<?php echo htmlspecialchars($product_data['harga']); ?>" min="0" required>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan Perubahan</button>
                            </div>
                        </form>
                    </div>
                </div>
                <?php
                } // End if ($product_data)
                mysqli_close($koneksi);
                ?>
            </div>
        </div>
    </div>

    <footer class="bg-dark text-white text-center p-3 mt-5">
        <p>&copy; <?php echo date("Y"); ?> Kue Balok Admin. All Rights Reserved.</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function previewImage(event) {
            const preview = document.getElementById('preview');
            const file = event.target.files[0];
            if (file) {
                preview.src = URL.createObjectURL(file);
                preview.style.display = 'block';
                preview.onload = function() {
                    URL.revokeObjectURL(preview.src); // free memory
                }
            } else {
                preview.src = "#";
                preview.style.display = 'none';
            }
        }
    </script>
</body>
</html>