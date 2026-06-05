<?php
require_once '../includes/db_connect.php';

$keyword = trim($_GET['q'] ?? '');
$department = trim($_GET['department'] ?? '');
$assignee = trim($_GET['assignee'] ?? '');
$status = $_GET['status'] ?? '';

$departments = $mysqli->query("SELECT DISTINCT ten_phong_ban FROM Du_an WHERE ten_phong_ban IS NOT NULL AND ten_phong_ban <> '' ORDER BY ten_phong_ban")->fetch_all(MYSQLI_ASSOC);
$employees = $mysqli->query("SELECT id, ten FROM Nhan_vien ORDER BY ten")->fetch_all(MYSQLI_ASSOC);

$sql = "SELECT Du_an.*, Nhan_vien.ten AS ten_nv FROM Du_an LEFT JOIN Nhan_vien ON Du_an.id_nhan_vien = Nhan_vien.id WHERE 1=1";
$params = [];
$types = "";

if ($keyword !== '') {
  $sql .= " AND (Du_an.ten_du_an LIKE ? OR Du_an.mo_ta LIKE ?)";
  $term = "%{$keyword}%";
  array_push($params, $term, $term);
  $types .= "ss";
}

if ($department !== '') {
  $sql .= " AND Du_an.ten_phong_ban = ?";
  $params[] = $department;
  $types .= "s";
}

if ($assignee !== '') {
  $sql .= " AND Du_an.id_nhan_vien = ?";
  $params[] = (int) $assignee;
  $types .= "i";
}

if ($status === 'active') {
  $sql .= " AND (Du_an.ngay_ket_thuc IS NULL OR Du_an.ngay_ket_thuc >= CURDATE())";
} elseif ($status === 'ended') {
  $sql .= " AND Du_an.ngay_ket_thuc < CURDATE()";
} elseif ($status === 'unassigned') {
  $sql .= " AND Du_an.id_nhan_vien IS NULL";
}

$sql .= " ORDER BY Du_an.id DESC";

if ($params) {
  $stmt = $mysqli->prepare($sql);
  $stmt->bind_param($types, ...$params);
  $stmt->execute();
  $projects = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
  $stmt->close();
} else {
  $projects = $mysqli->query($sql)->fetch_all(MYSQLI_ASSOC);
}

include '../includes/header.php';
?>

<div class="page-heading">
  <div>
    <h1>Dự án</h1>
    <p>Quản lý dự án, phòng ban phụ trách và nhân sự chính.</p>
  </div>
  <a href="them.php" class="btn btn-primary">Thêm dự án</a>
</div>

<div class="filters-card">
  <form method="GET" class="filters-form">
    <div class="form-group">
      <label>Tìm kiếm</label>
      <input type="text" name="q" class="form-control" value="<?= htmlspecialchars($keyword) ?>" placeholder="Tên hoặc mô tả dự án">
    </div>
    <div class="form-group">
      <label>Phòng ban</label>
      <select name="department" class="form-control">
        <option value="">Tất cả</option>
        <?php foreach ($departments as $row): ?>
          <option value="<?= htmlspecialchars($row['ten_phong_ban']) ?>" <?= $department === $row['ten_phong_ban'] ? 'selected' : '' ?>>
            <?= htmlspecialchars($row['ten_phong_ban']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="form-group">
      <label>Phụ trách</label>
      <select name="assignee" class="form-control">
        <option value="">Tất cả</option>
        <?php foreach ($employees as $employee): ?>
          <option value="<?= $employee['id'] ?>" <?= $assignee === (string) $employee['id'] ? 'selected' : '' ?>>
            <?= htmlspecialchars($employee['ten']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="form-group">
      <label>Trạng thái</label>
      <select name="status" class="form-control">
        <option value="">Tất cả</option>
        <option value="active" <?= $status === 'active' ? 'selected' : '' ?>>Đang mở</option>
        <option value="ended" <?= $status === 'ended' ? 'selected' : '' ?>>Đã kết thúc</option>
        <option value="unassigned" <?= $status === 'unassigned' ? 'selected' : '' ?>>Chưa gán</option>
      </select>
    </div>
    <button type="submit" class="btn btn-primary">Tìm kiếm</button>
  </form>
</div>

<div class="card">
  <div class="card-header">
    <h2>Danh sách dự án</h2>
  </div>
  <div class="table-container">
    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>Tên dự án</th>
          <th>Thời hạn</th>
          <th>Phòng ban</th>
          <th>Phụ trách</th>
          <th>Thao tác</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($projects as $p): ?>
          <tr>
            <td><?= $p['id'] ?></td>
            <td>
              <div class="text-strong"><?= htmlspecialchars($p['ten_du_an']) ?></div>
              <small class="text-muted"><?= htmlspecialchars($p['mo_ta']) ?></small>
            </td>
            <td>
              <div class="text-small">Bắt đầu: <?= $p['ngay_bat_dau'] ?></div>
              <div class="text-small-muted">Kết thúc: <?= $p['ngay_ket_thuc'] ?: 'Chưa có' ?></div>
            </td>
            <td><?= htmlspecialchars($p['ten_phong_ban']) ?></td>
            <td><?= $p['ten_nv'] ? htmlspecialchars($p['ten_nv']) : '<span class="badge badge-info">Chưa gán</span>' ?></td>
            <td>
              <div class="actions-row">
                <a href="sua.php?id=<?= $p['id'] ?>" class="btn btn-success">Sửa</a>
                <a href="xoa.php?id=<?= $p['id'] ?>" class="btn btn-danger" onclick="return confirm('Xóa dự án này?')">Xóa</a>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (empty($projects)): ?>
          <tr><td colspan="6" class="empty-state">Không tìm thấy dự án phù hợp.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include '../includes/footer.php'; ?>
