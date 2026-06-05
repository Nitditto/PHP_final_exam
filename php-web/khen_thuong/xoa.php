<?php
require_once '../includes/db_connect.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id > 0) {
  $stmt = $mysqli->prepare("DELETE FROM Danh_gia_khen_thuong WHERE id = ?");
  $stmt->bind_param("i", $id);
  $stmt->execute();
  $stmt->close();
}

header("Location: index.php");
exit();
