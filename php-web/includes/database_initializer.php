<?php
require_once __DIR__ . '/db_config.php';

function initialize_database()
{
  global $db_host, $db_username, $db_password;

  mysqli_report(MYSQLI_REPORT_OFF);

  // Connect without selecting a database because the SQL file creates it.
  $conn = mysqli_connect($db_host, $db_username, $db_password);

  if (!$conn) {
    return [
      'success' => false,
      'error' => 'Connection failed: ' . mysqli_connect_error(),
    ];
  }

  $init_query = file_get_contents(dirname(__DIR__) . '/quan_ly_nhan_su.sql');
  if ($init_query === false) {
    $conn->close();
    return [
      'success' => false,
      'error' => 'Không đọc được file quan_ly_nhan_su.sql.',
    ];
  }

  if (!$conn->multi_query($init_query)) {
    $error = $conn->error;
    $conn->close();
    return [
      'success' => false,
      'error' => 'Error running init query: ' . $error,
    ];
  }

  do {
    if ($result = $conn->store_result()) {
      $result->free();
    }
  } while ($conn->more_results() && $conn->next_result());

  $error = $conn->errno ? $conn->error : null;
  $conn->close();

  return [
    'success' => $error === null,
    'error' => $error,
  ];
}
