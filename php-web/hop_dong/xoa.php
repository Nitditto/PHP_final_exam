<?php
require_once '../includes/db_connect.php';
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($id > 0) {
  $mysqli->query("DELETE FROM Hop_dong_lao_dong WHERE id = $id");
}
header("Location: index.php");
exit();
