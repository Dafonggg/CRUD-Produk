<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Produk Kue Balok - Admin</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/2.3.0/css/dataTables.bootstrap5.min.css" /> <link rel="stylesheet" type="text/css" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="style.css" />
</head>

<body>
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
                        <a class="nav-link active" aria-current="page" href="index.php"><i class="fas fa-home"></i> Beranda</a>
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
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="page-title mb-0">Data Produk Kue Balok</h1>
            <a class="btn btn-primary" href="input.php"><i class="fas fa-plus"></i> Tambah Data Baru</a>
        </div>


        <?php
        // Display messages
        if (isset($_GET['pesan'])) {
            $pesan = $_GET['pesan'];
            $alert_type = "info";
            $message_text = "";

            if ($pesan == "input_sukses" || $pesan == "input") { // Accommodate old 'input' message
                $alert_type = "success";
                $message_text = "<strong>Berhasil!</strong> Data produk baru telah berhasil ditambahkan.";
            } else if ($pesan == "update_sukses" || $pesan == "update") { // Accommodate old 'update' message
                $alert_type = "success";
                $message_text = "<strong>Berhasil!</strong> Data produk telah berhasil diperbarui.";
            } else if ($pesan == "hapus_sukses" || $pesan == "hapus") { // Accommodate old 'hapus' message
                $alert_type = "success";
                $message_text = "<strong>Berhasil!</strong> Data produk telah berhasil dihapus.";
            } else if ($pesan == "hapus_gagal") {
                $alert_type = "danger";
                $message_text = "<strong>Gagal!</strong> Terjadi kesalahan saat menghapus data produk.";
            } else if ($pesan == "update_gagal") {
                $alert_type = "danger";
                $message_text = "<strong>Gagal!</strong> Terjadi kesalahan saat memperbarui data produk.";
            } else if ($pesan == "input_gagal") {
                $alert_type = "danger";
                $message_text = "<strong>Gagal!</strong> Terjadi kesalahan saat menambahkan data produk.";
            }


            if (!empty($message_text)) {
                echo "<div class='alert alert-{$alert_type} alert-dismissible fade show' role='alert'>
                        {$message_text}
                        <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                        </div>";
            }
        }
        ?>

        <div class="card">
            <div class="card-header custom-card-header">
                <i class="fas fa-list"></i> Daftar Produk
            </div>
            <div class="card-body">
                <table id="myTable" class="table table-striped table-bordered" style="width:100%">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>ID Kategori</th>
                            <th>Varian</th>
                            <th>Foto Produk</th>
                            <th>Stok</th>
                            <th>Harga</th>
                            <th>Opsi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        include "koneksi.php";
                        $query_mysql = mysqli_query($koneksi, "SELECT p.*, k.jenis as nama_kategori FROM produk p LEFT JOIN kategori k ON p.id_kategori = k.id_kategori");
                        if (!$query_mysql) {
                            echo "<tr><td colspan='7' class='text-center'>Error fetching data: " . mysqli_error($koneksi) . "</td></tr>";
                        } else {
                            $nomor = 1;
                            if (mysqli_num_rows($query_mysql) == 0) {
                                echo "<tr><td colspan='7' class='text-center'>Belum ada data produk.</td></tr>";
                            } else {
                                while ($data = mysqli_fetch_array($query_mysql)) {
                        ?>
                        <tr>
                            <td><?php echo $nomor++; ?></td>
                            <td><?php echo htmlspecialchars($data['id_kategori']) . (isset($data['nama_kategori']) ? ' - ' . htmlspecialchars($data['nama_kategori']) : ''); ?></td>
                            <td><?php echo htmlspecialchars($data['varian']); ?></td>
                            <td>
                                <?php if (!empty($data['foto']) && file_exists("foto/" . $data['foto'])): ?>
                                    <img src="foto/<?php echo htmlspecialchars($data['foto']); ?>" width="100" alt="<?php echo htmlspecialchars($data['varian']); ?>" class="img-thumbnail">
                                <?php else: ?>
                                    <img src="path/to/placeholder-image.png" width="100" alt="Tidak ada foto" class="img-thumbnail"> <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($data['stok']); ?></td>
                            <td>Rp <?php echo number_format($data['harga'], 0, ',', '.'); ?></td>
                            <td class="action-buttons">
                                <a class="btn btn-warning btn-sm" href="edit.php?id_produk=<?php echo $data['id_produk']; ?>" title="Edit"><i class="fas fa-edit"></i></a>
                                <a class="btn btn-danger btn-sm" href="hapus.php?id_produk=<?php echo $data['id_produk']; ?>" onclick="return confirm('Apakah Anda yakin ingin menghapus produk ini?');" title="Hapus"><i class="fas fa-trash"></i></a>
                            </td>
                        </tr>
                        <?php 
                                }
                            }
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <footer class="bg-dark text-white text-center p-3 mt-5 mt-auto">
        <p>&copy; <?php echo date("Y"); ?> Kue Balok Admin. All Rights Reserved.</p>
    </footer>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/2.3.0/js/dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/2.3.0/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function () {
            $('#myTable').DataTable({
                responsive: true,
                language: {
                    // Corrected URL for DataTables 2.x series plug-in
                    url: 'js/id.json', // Indonesian language for DataTables
                },
                columnDefs: [
                    { orderable: false, targets: [3, 6] } // Disable sorting for Foto and Opsi
                ]
            });
        });
    </script>
</body>
</html>