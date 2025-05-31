<?php
include 'koneksi.php';

// Default redirect location
$redirect_url = "index.php"; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate essential POST data
    if (!isset($_POST['id_produk'], $_POST['id_kategori'], $_POST['varian'], $_POST['stok'], $_POST['harga'])) {
        // This indicates a problem with the form submission or direct access
        // For a production system, log this error.
        // Redirect to index, or perhaps to the edit page with an error.
        // For simplicity here, we'll redirect to index with a generic error.
        header("Location: index.php?pesan=update_gagal_postdata");
        exit;
    }

    $id_produk = mysqli_real_escape_string($koneksi, $_POST['id_produk']);
    $id_kategori = mysqli_real_escape_string($koneksi, $_POST['id_kategori']);
    $varian = trim(mysqli_real_escape_string($koneksi, $_POST['varian']));
    $stok = mysqli_real_escape_string($koneksi, $_POST['stok']);
    $harga = mysqli_real_escape_string($koneksi, $_POST['harga']);
    $foto_lama = isset($_POST['foto_lama']) ? mysqli_real_escape_string($koneksi, $_POST['foto_lama']) : '';

    // Server-side validation
    if (empty($id_kategori) || empty($varian) || !is_numeric($stok) || $stok < 0 || !is_numeric($harga) || $harga < 0) {
        // Redirect back to the edit form with an error message
        header("Location: edit.php?id_produk=" . $id_produk . "&pesan=update_gagal_validation");
        exit;
    }

    $namaFileBaru = $foto_lama; // Assume old photo is kept unless new one is uploaded
    $folderTujuan = 'foto/';

    // Process new photo upload if a file is provided
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $foto = $_FILES['foto'];
        $namaFile = $foto['name'];
        $ukuranFile = $foto['size'];
        $tmpName = $foto['tmp_name'];
        $ekstensiValid = ['jpg', 'jpeg', 'png', 'gif'];
        $ekstensiFile = strtolower(pathinfo($namaFile, PATHINFO_EXTENSION));

        if (!in_array($ekstensiFile, $ekstensiValid)) {
            header("Location: edit.php?id_produk=" . $id_produk . "&pesan=update_gagal_filetype");
            exit;
        }
        if ($ukuranFile > 5 * 1024 * 1024) { // Max 5MB
            header("Location: edit.php?id_produk=" . $id_produk . "&pesan=update_gagal_filesize");
            exit;
        }

        // Generate new unique filename
        $namaFileBaru = uniqid('produk_') . '.' . $ekstensiFile;

        if (!is_dir($folderTujuan)) {
            if (!mkdir($folderTujuan, 0777, true)) {
                error_log("Failed to create upload directory: " . $folderTujuan);
                header("Location: edit.php?id_produk=" . $id_produk . "&pesan=update_gagal_uploaddir");
                exit;
            }
        }
        
        if (move_uploaded_file($tmpName, $folderTujuan . $namaFileBaru)) {
            // New photo uploaded successfully, delete the old one if it exists and is different
            if (!empty($foto_lama) && $foto_lama != $namaFileBaru && file_exists($folderTujuan . $foto_lama)) {
                unlink($folderTujuan . $foto_lama);
            }
        } else {
            error_log("Failed to move uploaded file: " . $namaFileBaru);
            // Failed to move new photo, keep the old one (or revert $namaFileBaru to $foto_lama)
            $namaFileBaru = $foto_lama; // Revert to old photo name as new upload failed
            // Optionally, set an error message, but for now, we'll proceed with the old photo
            // Consider how to handle this failure more gracefully - maybe redirect with a specific error.
            header("Location: edit.php?id_produk=" . $id_produk . "&pesan=update_gagal_uploadmove");
            exit;
        }
    } elseif (isset($_FILES['foto']) && $_FILES['foto']['error'] !== UPLOAD_ERR_NO_FILE && $_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
        // An actual upload error occurred
        error_log("File upload error code for update: " . $_FILES['foto']['error']);
        header("Location: edit.php?id_produk=" . $id_produk . "&pesan=update_gagal_uploaderror");
        exit;
    }
    // If $_FILES['foto']['error'] === UPLOAD_ERR_NO_FILE, it means no new file was uploaded, so $namaFileBaru remains $foto_lama.

    // Use prepared statement for the update
    $stmt = $koneksi->prepare("UPDATE produk SET id_kategori=?, varian=?, foto=?, stok=?, harga=? WHERE id_produk=?");
    if (!$stmt) {
        error_log("Prepare failed (UPDATE): (" . $koneksi->errno . ") " . $koneksi->error);
        header("Location: edit.php?id_produk=" . $id_produk . "&pesan=update_gagal_dbprepare");
        exit;
    }

    // Bind parameters: id_kategori, varian, foto_final, stok, harga, id_produk
    // Ensure types match: sssiis (string, string, string, integer, integer, string/integer for id_produk)
    $stmt->bind_param("sssisi", $id_kategori, $varian, $namaFileBaru, $stok, $harga, $id_produk);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            $redirect_url = "index.php?pesan=update_sukses";
        } else {
            // Query executed, but no rows were changed (e.g., data was the same)
             $redirect_url = "index.php?pesan=update_nochang"; // Or some other neutral message
        }
    } else {
        error_log("Execute failed (UPDATE): (" . $stmt->errno . ") " . $stmt->error);
        $redirect_url = "edit.php?id_produk=" . $id_produk . "&pesan=update_gagal_dbexecute";
    }
    $stmt->close();

} else {
    // If not POST, redirect to index (or an error page)
    $redirect_url = "index.php?pesan=invalid_request";
}

mysqli_close($koneksi);
header("Location: " . $redirect_url);
exit;
?>