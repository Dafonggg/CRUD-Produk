<?php
include 'koneksi.php';

// Default redirect
$redirect_url = "index.php"; 

if (isset($_GET['id_produk'])) {
    $id_produk = mysqli_real_escape_string($koneksi, $_GET['id_produk']);

    // Optional: Select the photo filename to delete it from the server
    $query_select_foto = mysqli_query($koneksi, "SELECT foto FROM produk WHERE id_produk='$id_produk'");
    if ($data_foto = mysqli_fetch_assoc($query_select_foto)) {
        $foto_to_delete = $data_foto['foto'];
    } else {
        $foto_to_delete = null; // Product not found or no photo
    }

    // Hapus data dari tabel produk
    $query_delete = mysqli_query($koneksi, "DELETE FROM produk WHERE id_produk='$id_produk'");

    if ($query_delete) {
        if (mysqli_affected_rows($koneksi) > 0) {
            // If delete was successful and a photo existed, try to delete it
            if ($foto_to_delete && !empty($foto_to_delete) && file_exists("foto/" . $foto_to_delete)) {
                unlink("foto/" . $foto_to_delete);
            }
            $redirect_url = "index.php?pesan=hapus_sukses";
        } else {
            // No rows affected, product might have been deleted already or ID was wrong
            $redirect_url = "index.php?pesan=hapus_gagal_notfound";
        }
    } else {
        // Query failed
        error_log("Delete query failed: " . mysqli_error($koneksi));
        $redirect_url = "index.php?pesan=hapus_gagal_db";
    }
} else {
    // Jika tidak ada id_produk
    $redirect_url = "index.php?pesan=hapus_gagal_noid";
}

mysqli_close($koneksi);
header("Location: " . $redirect_url);
exit;
?>
