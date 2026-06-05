<?php
require_once '../includes/db_connect.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id > 0) {
  $sql = "DELETE FROM Ca_lam WHERE id = ?";
  if ($stmt = $mysqli->prepare($sql)) {
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
  }
}
header("Location: index.php");
exit();
?>