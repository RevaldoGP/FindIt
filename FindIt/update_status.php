<?php
require "db.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $report_id = $_POST['report_id'];
    $status = $_POST['status'];

    // Admin tidak bisa mengubah menjadi Pending → jadi tidak perlu dicek
    $query = $conn->prepare("UPDATE reports SET status = ? WHERE report_id = ?");
    $query->bind_param("si", $status, $report_id);
    $query->execute();

    header("Location: admin.php");
    exit;
}
