<?php
include 'koneksi.php';

// Default redirect in case of issues before processing
$redirect_url = "input.php?pesan=input_gagal_unknown"; // A generic unknown error

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Cek data POST
    if (!isset($_POST['id_kategori'], $_POST['varian'], $_POST['stok'], $_POST['harga'])) {
        // It's better to redirect back to the form with an error if core data is missing.
        // For production, you might log this as it indicates a potential bypass of client-side validation or form tampering.
        header("Location: input.php?pesan=input_gagal_incomplete");
        exit;
    }

    $id_kategori = $_POST['id_kategori'];
    $varian = trim($_POST['varian']); // Trim whitespace
    $stok = $_POST['stok'];
    $harga = $_POST['harga'];
    $foto = $_FILES['foto']; // File upload data

    // Validasi sederhana server-side
    if (empty($id_kategori) || empty($varian) || !is_numeric($stok) || $stok < 0 || !is_numeric($harga) || $harga < 0) {
        // Redirect back with a more specific error
        header("Location: input.php?pesan=input_gagal_validation");
        exit;
    }

    $namaFileBaru = ''; // Initialize
    // Cek apakah ada file yang diupload dan tidak ada error
    if (isset($foto) && $foto['error'] === UPLOAD_ERR_OK) {
        $namaFile = $foto['name'];
        $ukuranFile = $foto['size'];
        $tmpName = $foto['tmp_name'];
        $ekstensiValid = ['jpg', 'jpeg', 'png', 'gif'];
        $ekstensiFile = strtolower(pathinfo($namaFile, PATHINFO_EXTENSION));

        if (!in_array($ekstensiFile, $ekstensiValid)) {
            header("Location: input.php?pesan=input_gagal_filetype");
            exit;
        }
        if ($ukuranFile > 5 * 1024 * 1024) { // Max 5MB
            header("Location: input.php?pesan=input_gagal_filesize");
            exit;
        }

        $namaFileBaru = uniqid('produk_') . '.' . $ekstensiFile; // Prefix to avoid collisions
        $folderTujuan = 'foto/';

        if (!is_dir($folderTujuan)) {
            if (!mkdir($folderTujuan, 0777, true)) {
                 // Log this error for server admin
                error_log("Failed to create upload directory: " . $folderTujuan);
                header("Location: input.php?pesan=input_gagal_uploaddir");
                exit;
            }
        }
        
        if (!move_uploaded_file($tmpName, $folderTujuan . $namaFileBaru)) {
            // Log this error
            error_log("Failed to move uploaded file: " . $namaFileBaru);
            header("Location: input.php?pesan=input_gagal_uploadmove");
            exit;
        }
    } elseif (isset($foto) && $foto['error'] !== UPLOAD_ERR_NO_FILE && $foto['error'] !== UPLOAD_ERR_OK) {
        // An actual upload error occurred, other than no file being submitted
        // Log $foto['error'] for debugging
        error_log("File upload error code: " . $foto['error']);
        header("Location: input.php?pesan=input_gagal_uploaderror");
        exit;
    }


    // Gunakan prepared statement
    $stmt = $koneksi->prepare("INSERT INTO produk (id_kategori, varian, foto, stok, harga) VALUES (?, ?, ?, ?, ?)");
    if (!$stmt) {
        // Log this error
        error_log("Prepare failed: (" . $koneksi->errno . ") " . $koneksi->error);
        header("Location: input.php?pesan=input_gagal_dbprepare");
        exit;
    }

    // Bind parameters: id_kategori (can be string or int depending on DB, assume string for flexibility here if it's like 'K001'), varian string, foto string, stok integer, harga integer
    // Ensure your database column types match these. 'id_kategori' is often VARCHAR.
    $stmt->bind_param("sssii", $id_kategori, $varian, $namaFileBaru, $stok, $harga);

    if ($stmt->execute()) {
        $redirect_url = "index.php?pesan=input_sukses";
    } else {
        // Log this error
        error_log("Execute failed: (" . $stmt->errno . ") " . $stmt->error);
        $redirect_url = "input.php?pesan=input_gagal_dbexecute";
    }
    $stmt->close();
} else {
    // If not POST, redirect to form or home
    $redirect_url = "index.php";
}

$koneksi->close();
header("Location: " . $redirect_url);
exit;
?>