<?php
require_once __DIR__ . '/db_config.php';

$mysqli = new mysqli($db_host, $db_username, $db_password, $db_name);
if ($mysqli->connect_error) {
  die("Kết nối cơ sở dữ liệu thất bại: " . $mysqli->connect_error);
}
$mysqli->set_charset("utf8");
