<?php
require_once __DIR__ . '/includes/db_config.php';
require_once __DIR__ . '/includes/database_initializer.php';

mysqli_report(MYSQLI_REPORT_OFF);

function connect_database()
{
  global $db_host, $db_username, $db_password, $db_name;

  return @new mysqli($db_host, $db_username, $db_password, $db_name);
}

function database_has_required_tables($mysqli)
{
  $required_tables = [
    'Nhan_vien',
    'Ca_lam',
    'Du_an',
    'Hop_dong_lao_dong',
    'Danh_gia_khen_thuong',
  ];

  foreach ($required_tables as $table) {
    $escaped_table = $mysqli->real_escape_string($table);
    $result = $mysqli->query("SHOW TABLES LIKE '{$escaped_table}'");
    if (!$result || $result->num_rows === 0) {
      return false;
    }
  }

  return true;
}

function dashboard_value($mysqli, $sql, $default = 0)
{
  $result = $mysqli->query($sql);
  if (!$result) {
    return $default;
  }

  $row = $result->fetch_row();
  return $row ? $row[0] : $default;
}

function dashboard_distribution($mysqli, $sql)
{
  $result = $mysqli->query($sql);
  if (!$result) {
    return [];
  }

  $items = [];
  while ($row = $result->fetch_assoc()) {
    $items[] = [
      'label' => $row['label'],
      'value' => (int) $row['total'],
    ];
  }

  return $items;
}

function dashboard_total($items)
{
  return array_sum(array_column($items, 'value'));
}

function dashboard_pie_segments($items)
{
  $total = dashboard_total($items);
  if ($total <= 0) {
    return [];
  }

  $palette = ['#929292ff', '#25eb74ff', '#10b981', '#f59e0b', '#ef4444', '#7c3aed', '#14b8a6', '#f97316'];
  $segments = [];
  $offset = 25;

  foreach ($items as $index => $item) {
    $size = round(($item['value'] / $total) * 100, 2);
    $segments[] = [
      'label' => $item['label'],
      'value' => $item['value'],
      'percent' => round(($item['value'] / $total) * 100, 1),
      'color' => $palette[$index % count($palette)],
      'size' => $size,
      'remainder' => round(100 - $size, 2),
      'offset' => round($offset, 2),
    ];
    $offset -= $size;
  }

  return $segments;
}

$init_error = null;
$mysqli = connect_database();
$db_error = $mysqli->connect_error;

if (!$db_error && !database_has_required_tables($mysqli)) {
  $db_error = 'Database schema is missing.';
}

if ($db_error) {
  $init_result = initialize_database();

  if ($init_result['success']) {
    $mysqli = connect_database();
    $db_error = $mysqli->connect_error;
  } else {
    $init_error = $init_result['error'];
  }
}

if (!$db_error) {
  $mysqli->set_charset("utf8");
}

$stats = [];
$recent_employees = [];
$active_contracts = [];
$role_distribution = [];
$department_distribution = [];

if (!$db_error) {
  $stats = [
    'employees' => dashboard_value($mysqli, "SELECT COUNT(*) FROM Nhan_vien"),
    'departments' => dashboard_value($mysqli, "SELECT COUNT(DISTINCT phong_ban) FROM Nhan_vien WHERE phong_ban IS NOT NULL AND phong_ban <> ''"),
    'team_leads' => dashboard_value($mysqli, "SELECT COUNT(*) FROM Nhan_vien WHERE truong_ban = 1"),
    'avg_salary' => dashboard_value($mysqli, "SELECT AVG(luong) FROM Nhan_vien"),
    'today_shifts' => dashboard_value($mysqli, "SELECT COUNT(*) FROM Ca_lam WHERE ngay_lam = CURDATE()"),
    'overtime_shifts' => dashboard_value($mysqli, "SELECT COUNT(*) FROM Ca_lam WHERE tang_ca = 1"),
    'active_projects' => dashboard_value($mysqli, "SELECT COUNT(*) FROM Du_an WHERE ngay_ket_thuc IS NULL OR ngay_ket_thuc >= CURDATE()"),
    'active_contracts' => dashboard_value($mysqli, "SELECT COUNT(*) FROM Hop_dong_lao_dong WHERE trang_thai = 'Đang hiệu lực'"),
    'monthly_awards' => dashboard_value($mysqli, "SELECT COALESCE(SUM(so_tien), 0) FROM Danh_gia_khen_thuong WHERE MONTH(ngay_ghi_nhan) = MONTH(CURDATE()) AND YEAR(ngay_ghi_nhan) = YEAR(CURDATE())"),
  ];

  $employees_result = $mysqli->query("SELECT ten, phong_ban, chuc_vu, luong FROM Nhan_vien ORDER BY id DESC LIMIT 5");
  if ($employees_result) {
    $recent_employees = $employees_result->fetch_all(MYSQLI_ASSOC);
  }

  $contracts_result = $mysqli->query(
    "SELECT Hop_dong_lao_dong.loai_hop_dong, Hop_dong_lao_dong.ngay_ket_thuc, Nhan_vien.ten
     FROM Hop_dong_lao_dong
     JOIN Nhan_vien ON Hop_dong_lao_dong.id_nhan_vien = Nhan_vien.id
     WHERE Hop_dong_lao_dong.trang_thai = 'Đang hiệu lực'
     ORDER BY Hop_dong_lao_dong.ngay_ket_thuc IS NULL, Hop_dong_lao_dong.ngay_ket_thuc ASC
     LIMIT 5"
  );
  if ($contracts_result) {
    $active_contracts = $contracts_result->fetch_all(MYSQLI_ASSOC);
  }

  $role_distribution = dashboard_distribution(
    $mysqli,
    "SELECT COALESCE(NULLIF(TRIM(chuc_vu), ''), 'Chưa có chức vụ') AS label, COUNT(*) AS total
     FROM Nhan_vien
     GROUP BY label
     ORDER BY total DESC, label ASC"
  );

  $department_distribution = dashboard_distribution(
    $mysqli,
    "SELECT COALESCE(NULLIF(TRIM(phong_ban), ''), 'Chưa có phòng ban') AS label, COUNT(*) AS total
     FROM Nhan_vien
     GROUP BY label
     ORDER BY total DESC, label ASC"
  );
}

$role_segments = dashboard_pie_segments($role_distribution);
$role_total = dashboard_total($role_distribution);
$department_max = $department_distribution ? max(array_column($department_distribution, 'value')) : 0;

include __DIR__ . '/includes/header.php';
?>

<?php if ($db_error): ?>
  <div class="card">
    <h2>Chưa kết nối được cơ sở dữ liệu</h2>
    <p class="muted">
      <?= htmlspecialchars($init_error ?: $db_error) ?>
    </p>
  </div>
<?php else: ?>
  <div class="page-heading">
    <div>
      <h1>Dashboard</h1>
      <p>Tổng quan nhanh về nhân sự, ca làm, dự án và hợp đồng.</p>
    </div>
  </div>

  <div class="stats-grid">
    <div class="metric-card">
      <span>Nhân viên</span>
      <strong><?= number_format($stats['employees']) ?></strong>
    </div>
    <div class="metric-card">
      <span>Phòng ban</span>
      <strong><?= number_format($stats['departments']) ?></strong>
    </div>
    <div class="metric-card">
      <span>Trưởng ban</span>
      <strong><?= number_format($stats['team_leads']) ?></strong>
    </div>
    <div class="metric-card">
      <span>Lương trung bình</span>
      <strong><?= number_format((float) $stats['avg_salary']) ?> đ</strong>
    </div>
    <div class="metric-card">
      <span>Ca làm hôm nay</span>
      <strong><?= number_format($stats['today_shifts']) ?></strong>
    </div>
    <div class="metric-card">
      <span>Ca tăng ca</span>
      <strong><?= number_format($stats['overtime_shifts']) ?></strong>
    </div>
    <div class="metric-card">
      <span>Dự án đang mở</span>
      <strong><?= number_format($stats['active_projects']) ?></strong>
    </div>
    <div class="metric-card">
      <span>HĐ hiệu lực</span>
      <strong><?= number_format($stats['active_contracts']) ?></strong>
    </div>
  </div>

  <div class="chart-grid">
    <section class="card">
      <div class="card-header">
        <div>
          <h3>Phân bố chức vụ</h3>
          <p class="muted">Pie chart theo số lượng nhân viên ở từng vai trò.</p>
        </div>
      </div>
      <?php if ($role_segments): ?>
        <div class="pie-chart-layout">
          <div class="donut-chart-wrap">
            <svg viewBox="0 0 42 42" class="donut-chart" aria-label="Biểu đồ phân bố chức vụ">
              <circle cx="21" cy="21" r="15.915" class="donut-track"></circle>
              <?php foreach ($role_segments as $segment): ?>
                <circle cx="21" cy="21" r="15.915" class="donut-segment" stroke="<?= htmlspecialchars($segment['color']) ?>"
                  stroke-dasharray="<?= htmlspecialchars((string) $segment['size']) ?> <?= htmlspecialchars((string) $segment['remainder']) ?>"
                  stroke-dashoffset="<?= htmlspecialchars((string) $segment['offset']) ?>"></circle>
              <?php endforeach; ?>
            </svg>
            <div class="donut-center">
              <strong><?= number_format($role_total) ?></strong>
              <span>nhân viên</span>
            </div>
          </div>
          <div class="chart-legend">
            <?php foreach ($role_segments as $segment): ?>
              <div class="legend-item">
                <span class="legend-swatch" style="background-color: <?= htmlspecialchars($segment['color']) ?>;"></span>
                <div class="legend-copy">
                  <strong><?= htmlspecialchars($segment['label']) ?></strong>
                  <span><?= number_format($segment['value']) ?> người,
                    <?= rtrim(rtrim(number_format($segment['percent'], 1), '0'), '.') ?>%</span>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      <?php else: ?>
        <div class="chart-empty">Chưa có dữ liệu chức vụ để hiển thị.</div>
      <?php endif; ?>
    </section>

    <section class="card">
      <div class="card-header">
        <div>
          <h3>Nhân sự theo phòng ban</h3>
          <p class="muted">Bar chart số lượng nhân viên ở từng phòng ban.</p>
        </div>
      </div>
      <?php if ($department_distribution): ?>
        <div class="bar-chart">
          <?php foreach ($department_distribution as $department_item): ?>
            <?php
            $width = $department_max > 0 ? ($department_item['value'] / $department_max) * 100 : 0;
            ?>
            <div class="bar-row">
              <div class="bar-label"><?= htmlspecialchars($department_item['label']) ?></div>
              <div class="bar-track">
                <div class="bar-fill" style="width: <?= htmlspecialchars(number_format($width, 2, '.', '')) ?>%;"></div>
              </div>
              <div class="bar-value"><?= number_format($department_item['value']) ?></div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <div class="chart-empty">Chưa có dữ liệu phòng ban để hiển thị.</div>
      <?php endif; ?>
    </section>
  </div>

  <div class="dashboard-grid">
    <div class="card">
      <h3>Nhân viên mới</h3>
      <div class="table-container">
        <table>
          <thead>
            <tr>
              <th>Họ tên</th>
              <th>Phòng ban</th>
              <th>Chức vụ</th>
              <th>Lương</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($recent_employees as $employee): ?>
              <tr>
                <td><?= htmlspecialchars($employee['ten']) ?></td>
                <td><?= htmlspecialchars($employee['phong_ban'] ?: 'Chưa có') ?></td>
                <td><?= htmlspecialchars($employee['chuc_vu'] ?: 'Chưa có') ?></td>
                <td><?= number_format((float) $employee['luong']) ?> đ</td>
              </tr>
            <?php endforeach; ?>
            <?php if (empty($recent_employees)): ?>
              <tr>
                <td colspan="4">Chưa có nhân viên.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <div class="card">
      <h3>Hợp đồng đang hiệu lực</h3>
      <div class="table-container">
        <table>
          <thead>
            <tr>
              <th>Nhân viên</th>
              <th>Loại HĐ</th>
              <th>Ngày kết thúc</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($active_contracts as $contract): ?>
              <tr>
                <td><?= htmlspecialchars($contract['ten']) ?></td>
                <td><?= htmlspecialchars($contract['loai_hop_dong']) ?></td>
                <td><?= htmlspecialchars($contract['ngay_ket_thuc'] ?: 'Không thời hạn') ?></td>
              </tr>
            <?php endforeach; ?>
            <?php if (empty($active_contracts)): ?>
              <tr>
                <td colspan="3">Chưa có hợp đồng hiệu lực.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <div class="card">
    <h3>Khen thưởng tháng này</h3>
    <div class="summary-row">
      <div>
        <span class="summary-label">Tổng giá trị ghi nhận</span>
        <strong><?= number_format((float) $stats['monthly_awards']) ?> đ</strong>
      </div>
      <a href="<?= htmlspecialchars($nav_prefix) ?>khen_thuong/index.php" class="btn btn-primary">Xem chi tiết</a>
    </div>
  </div>
<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>