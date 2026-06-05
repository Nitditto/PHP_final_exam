<?php
require_once __DIR__ . '/includes/database_initializer.php';

$init_result = initialize_database();

if ($init_result['success']) {
  header("Location: index.php?initialized=1");
  exit();
}

die($init_result['error']);
