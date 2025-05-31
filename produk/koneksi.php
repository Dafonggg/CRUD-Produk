<?php
$host = "localhost";
$username = "root";
$password = "";
$database = "kue_balok";


// Membuat koneksi ke database
$koneksi = mysqli_connect("localhost", "root", "", "kue_balok");
$koneksi = mysqli_connect($host, $username, $password, $database);



// Cek koneksi
// if (!$koneksi) {
//     die("Koneksi gagal: " . mysqli_connect_error());
// } else {
//     echo"koneksi berhasil";
// }

// Jika koneksi berhasil, Anda bisa lanjutkan proses lainnya di sini
// echo "Koneksi berhasil";
?>
