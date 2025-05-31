<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Tambah Produk Baru - Kue Balok Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="style.css"/>
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
                        <a class="nav-link active" aria-current="page" href="input.php"><i class="fas fa-plus-circle"></i> Tambah Produk</a>
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
                <div class="d-flex justify-content-between align-items-center">
                    <h1 class="page-title mb-4 text-start">Tambah Produk Baru</h1>
                    <a href="index.php" class="btn btn-outline-secondary mb-4"><i class="fas fa-arrow-left"></i> Kembali ke Daftar</a>
                </div>

                <div class="card">
                    <div class="card-header custom-card-header">
                        <i class="fas fa-pen"></i> Silakan Isi Data Produk
                    </div>
                    <div class="card-body">
                        <form action="input-aksi.php" method="post" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label for="id_kategori" class="form-label">Kategori <span class="text-danger">*</span></label>
                                <select name="id_kategori" id="id_kategori" class="form-select" required>
                                    <option value="" disabled selected>Pilih Kategori...</option>
                                    <?php
                                    include "koneksi.php"; // Ensure koneksi.php is included
                                    $result = mysqli_query($koneksi, "SELECT id_kategori, jenis FROM kategori ORDER BY jenis ASC");
                                    if ($result) {
                                        while ($row = mysqli_fetch_assoc($result)) {
                                            echo '<option value="' . htmlspecialchars($row['id_kategori']) . '">' . htmlspecialchars($row['id_kategori']) . ' - ' . htmlspecialchars($row['jenis']) . '</option>';
                                        }
                                    }
                                    ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="varian" class="form-label">Varian Produk <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="varian" id="varian" placeholder="Contoh: Cokelat Keju" required/>
                            </div>

                            <div class="mb-3">
                                <label for="foto" class="form-label">Foto Produk</label>
                                <input type="file" class="form-control" name="foto" id="foto" accept="image/png, image/jpeg, image/gif" onchange="previewImage(event)" />
                                <small class="form-text text-muted">Format: JPG, JPEG, PNG, GIF. Maks: 5MB.</small>
                                <img id="preview" src="#" alt="Preview Foto" style="display:none; margin-top:10px;" class="img-thumbnail"/>
                            </div>

                            <div class="mb-3">
                                <label for="stok" class="form-label">Stok <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" name="stok" id="stok" placeholder="0" min="0" required/>
                            </div>

                            <div class="mb-3">
                                <label for="harga" class="form-label">Harga (Rp) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" name="harga" id="harga" placeholder="15000" min="0" required/>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan Produk</button>
                            </div>
                        </form>
                    </div>
                </div>
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