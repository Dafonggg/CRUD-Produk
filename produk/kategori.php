<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include 'koneksi.php'; // Sertakan file koneksi database

$pesan = ''; // Variabel untuk menyimpan pesan feedback

// Logika untuk memproses penambahan kategori baru
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah_kategori'])) {
    $id_kategori_baru = mysqli_real_escape_string($koneksi, trim($_POST['id_kategori']));
    $jenis_kategori_baru = mysqli_real_escape_string($koneksi, trim($_POST['jenis_kategori']));
    $panjang_maksimum_jenis = 100; // Sesuaikan dengan batas kolom 'jenis' di DB Anda

    if (empty($id_kategori_baru) || empty($jenis_kategori_baru)) {
        $pesan = "<div class='alert alert-danger alert-dismissible fade show' role='alert'>
                    <strong>Gagal!</strong> ID Kategori dan Jenis Kategori tidak boleh kosong.
                    <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                  </div>";
    } elseif (strlen($jenis_kategori_baru) > $panjang_maksimum_jenis) {
        $pesan = "<div class='alert alert-danger alert-dismissible fade show' role='alert'>
                    <strong>Gagal!</strong> Nama Kategori terlalu panjang. Maksimal {$panjang_maksimum_jenis} karakter.
                    <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                  </div>";
    } else {
        $cek_id_query = "SELECT id_kategori FROM kategori WHERE id_kategori = '$id_kategori_baru'";
        $cek_id_result = mysqli_query($koneksi, $cek_id_query);

        if (mysqli_num_rows($cek_id_result) > 0) {
            $pesan = "<div class='alert alert-danger alert-dismissible fade show' role='alert'>
                        <strong>Gagal!</strong> ID Kategori '{$id_kategori_baru}' sudah digunakan.
                        <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                      </div>";
        } else {
            $insert_query = "INSERT INTO kategori (id_kategori, jenis) VALUES ('$id_kategori_baru', '$jenis_kategori_baru')";
            if (mysqli_query($koneksi, $insert_query)) {
                $pesan = "<div class='alert alert-success alert-dismissible fade show' role='alert'>
                            <strong>Berhasil!</strong> Kategori '{$jenis_kategori_baru}' telah ditambahkan.
                            <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                          </div>";
            } else {
                $pesan = "<div class='alert alert-danger alert-dismissible fade show' role='alert'>
                            <strong>Gagal!</strong> Error: " . mysqli_error($koneksi) . "
                            <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                          </div>";
            }
        }
    }
}

// Logika untuk menghapus kategori
if (isset($_GET['aksi']) && $_GET['aksi'] == 'hapus' && isset($_GET['id'])) {
    $id_kategori_hapus = mysqli_real_escape_string($koneksi, $_GET['id']);

    // Opsional: Cek apakah kategori digunakan oleh produk sebelum menghapus
    $cek_produk_query = "SELECT COUNT(*) as total_produk FROM produk WHERE id_kategori = '$id_kategori_hapus'";
    $cek_produk_result = mysqli_query($koneksi, $cek_produk_query);
    $data_produk = mysqli_fetch_assoc($cek_produk_result);

    if ($data_produk['total_produk'] > 0) {
        $pesan = "<div class='alert alert-warning alert-dismissible fade show' role='alert'>
                    <strong>Gagal Hapus!</strong> Kategori ini masih digunakan oleh ".$data_produk['total_produk']." produk. Hapus atau ubah produk terkait terlebih dahulu.
                    <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                  </div>";
    } else {
        $delete_query = "DELETE FROM kategori WHERE id_kategori = '$id_kategori_hapus'";
        if (mysqli_query($koneksi, $delete_query)) {
            if (mysqli_affected_rows($koneksi) > 0) {
                 $pesan = "<div class='alert alert-success alert-dismissible fade show' role='alert'>
                            <strong>Berhasil!</strong> Kategori telah dihapus.
                            <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                          </div>";
            } else {
                $pesan = "<div class='alert alert-warning alert-dismissible fade show' role='alert'>
                            <strong>Info!</strong> Kategori tidak ditemukan atau sudah dihapus.
                            <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                          </div>";
            }

        } else {
            $pesan = "<div class='alert alert-danger alert-dismissible fade show' role='alert'>
                        <strong>Gagal!</strong> Error: " . mysqli_error($koneksi) . "
                        <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                      </div>";
        }
    }
}

// Ambil pesan dari URL jika ada (setelah redirect dari edit_kategori.php)
if(isset($_GET['pesan'])){
    if($_GET['pesan'] == 'update_sukses'){
        $pesan = "<div class='alert alert-success alert-dismissible fade show' role='alert'>
                    <strong>Berhasil!</strong> Data kategori telah diperbarui.
                    <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                  </div>";
    } elseif($_GET['pesan'] == 'update_gagal'){
         $pesan = "<div class='alert alert-danger alert-dismissible fade show' role='alert'>
                    <strong>Gagal!</strong> Terjadi kesalahan saat memperbarui data kategori.
                    <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                  </div>";
    }
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Kategori - Kue Balok Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/2.3.0/css/dataTables.bootstrap5.min.css" />
    <link rel="stylesheet" type="text/css" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="d-flex flex-column min-vh-100">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php"><img src="img/logokecil30px.png" alt="LOGO KUE BALOK MANG WIRO" class="navbar-logo"></i> Kue Balok Admin</a>
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
        <h1 class="page-title">Manajemen Kategori Produk</h1>

        <?php echo $pesan; ?>

        <div class="card mb-4">
            <div class="card-header custom-card-header">
                <i class="fas fa-plus"></i> Tambah Kategori Baru
            </div>
            <div class="card-body">
                <form action="kategori.php" method="post">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="id_kategori" class="form-label">ID Kategori <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="id_kategori" id="id_kategori" placeholder="Contoh: K001" required>
                            <small class="form-text text-muted">Pastikan ID unik.</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="jenis_kategori" class="form-label">Jenis/Nama Kategori <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="jenis_kategori" id="jenis_kategori" placeholder="Contoh: Original" required>
                        </div>
                        <div class="col-md-2 mb-3 d-flex align-items-end">
                            <button type="submit" name="tambah_kategori" class="btn btn-primary w-100"><i class="fas fa-save"></i> Simpan</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header custom-card-header">
                <i class="fas fa-list"></i> Daftar Kategori Tersedia
            </div>
            <div class="card-body">
                <table id="tabelKategori" class="table table-striped table-bordered" style="width:100%">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>ID Kategori</th>
                            <th>Jenis/Nama Kategori</th>
                            <th>Opsi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $query_kategori = mysqli_query($koneksi, "SELECT * FROM kategori ORDER BY id_kategori ASC");
                        if ($query_kategori) {
                            if (mysqli_num_rows($query_kategori) == 0) {
                                echo "<tr><td colspan='4' class='text-center'>Belum ada data kategori.</td></tr>";
                            } else {
                                $nomor = 1;
                                while ($data = mysqli_fetch_assoc($query_kategori)) {
                        ?>
                        <tr>
                            <td><?php echo $nomor++; ?></td>
                            <td><?php echo htmlspecialchars($data['id_kategori']); ?></td>
                            <td><?php echo htmlspecialchars($data['jenis']); ?></td>
                            <td class="action-buttons">
                                <a href="edit_kategori.php?id=<?php echo urlencode($data['id_kategori']); ?>" class="btn btn-warning btn-sm" title="Edit"><i class="fas fa-edit"></i></a>
                                <a href="kategori.php?aksi=hapus&id=<?php echo urlencode($data['id_kategori']); ?>" class="btn btn-danger btn-sm" title="Hapus" onclick="return confirm('Yakin ingin menghapus kategori ini?');"><i class="fas fa-trash"></i></a>
                            </td>
                        </tr>
                        <?php
                                }
                            }
                        } else {
                            echo "<tr><td colspan='4' class='text-center'>Error mengambil data kategori: " . mysqli_error($koneksi) . "</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <footer class="bg-dark text-white text-center p-3 mt-auto">
        <p class="mb-0">&copy; <?php echo date("Y"); ?> Kue Balok Admin. All Rights Reserved.</p>
    </footer>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/2.3.0/js/dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/2.3.0/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function () {
            $('#tabelKategori').DataTable({
                responsive: true,
                language: {
                    url: 'js/id.json'
                },
                columnDefs: [
                  { orderable: false, targets: 3 } // Kolom Opsi (index 3) tidak bisa di-sort
                ]
            });
        });
    </script>
</body>
</html>