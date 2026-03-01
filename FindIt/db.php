<?php
$host = "localhost";
$user = "root"; 
$pass = ""; 
$dbname = "findit"; // sesuaikan nama database kamu

$conn = new mysqli($host, $user, $pass, $dbname);

// Cek error koneksi
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
