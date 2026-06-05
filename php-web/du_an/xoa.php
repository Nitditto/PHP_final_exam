<?php
require_once '../includes/db_connect.php';
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($id > 0) {
  $mysqli->query("DELETE FROM Du_an WHERE id = $id");
}
header("Location: index.php");
exit();
