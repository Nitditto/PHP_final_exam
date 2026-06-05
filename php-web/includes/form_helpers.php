<?php

function validate_date_range($start_date, $end_date, $start_label = 'Ngày bắt đầu', $end_label = 'Ngày kết thúc')
{
  $start_date = trim((string) $start_date);
  $end_date = trim((string) $end_date);

  if ($end_date !== '' && $start_date === '') {
    return "{$start_label} là bắt buộc khi có {$end_label}.";
  }

  if ($start_date === '' || $end_date === '') {
    return null;
  }

  $start = DateTime::createFromFormat('Y-m-d', $start_date);
  $end = DateTime::createFromFormat('Y-m-d', $end_date);

  $is_start_valid = $start && $start->format('Y-m-d') === $start_date;
  $is_end_valid = $end && $end->format('Y-m-d') === $end_date;

  if (!$is_start_valid || !$is_end_valid) {
    return 'Ngày không hợp lệ.';
  }

  if ($start >= $end) {
    return "{$start_label} phải nhỏ hơn {$end_label}.";
  }

  return null;
}

function award_type_options()
{
  return [
    'Thưởng tháng',
    'Thưởng hiệu suất',
    'Thưởng dự án',
    'Thưởng chuyên cần',
    'Thưởng sáng kiến',
    'Kỷ luật',
  ];
}

function award_type_options_with_selected($selected_value)
{
  $options = award_type_options();
  $selected_value = trim((string) $selected_value);

  if ($selected_value !== '' && !in_array($selected_value, $options, true)) {
    $options[] = $selected_value;
  }

  return $options;
}

function distinct_text_values($mysqli, $table_name, $column_name)
{
  $allowed_sources = [
    'Nhan_vien.phong_ban',
    'Nhan_vien.chuc_vu',
    'Du_an.ten_phong_ban',
  ];

  $source_key = $table_name . '.' . $column_name;
  if (!in_array($source_key, $allowed_sources, true)) {
    return [];
  }

  $sql = "SELECT DISTINCT {$column_name} AS value
          FROM {$table_name}
          WHERE {$column_name} IS NOT NULL AND TRIM({$column_name}) <> ''
          ORDER BY {$column_name}";

  $result = $mysqli->query($sql);
  if (!$result) {
    return [];
  }

  return array_map(static function ($row) {
    return $row['value'];
  }, $result->fetch_all(MYSQLI_ASSOC));
}

function merged_text_options(...$option_sets)
{
  $merged = [];

  foreach ($option_sets as $option_set) {
    foreach ($option_set as $option) {
      $option = trim((string) $option);
      if ($option !== '') {
        $merged[$option] = true;
      }
    }
  }

  $options = array_keys($merged);
  natcasesort($options);

  return array_values($options);
}

function manager_options($mysqli, $selected_id = null)
{
  $selected_id = $selected_id !== null && $selected_id !== '' ? (int) $selected_id : null;
  $managers = [];

  $result = $mysqli->query("SELECT id, ten FROM Nhan_vien WHERE truong_ban = 1 ORDER BY ten");
  if ($result) {
    $managers = $result->fetch_all(MYSQLI_ASSOC);
  }

  if ($selected_id === null) {
    return $managers;
  }

  foreach ($managers as $manager) {
    if ((int) $manager['id'] === $selected_id) {
      return $managers;
    }
  }

  $stmt = $mysqli->prepare("SELECT id, ten FROM Nhan_vien WHERE id = ?");
  $stmt->bind_param("i", $selected_id);
  $stmt->execute();
  $selected_employee = $stmt->get_result()->fetch_assoc();
  $stmt->close();

  if ($selected_employee) {
    $managers[] = $selected_employee;
  }

  usort($managers, static function ($left, $right) {
    return strcasecmp($left['ten'], $right['ten']);
  });

  return $managers;
}
