<?php
require_once '../includes/db_connect.php';
// get data from form
$keyword = trim($_GET['q'] ?? '');
$date_from = trim($_GET['date_from'] ?? '');
$date_to = trim($_GET['date_to'] ?? '');
$tang_ca = $_GET['tang_ca'] ?? '';

$sql = "SELECT Ca_lam.*, Nhan_vien.ten FROM Ca_lam JOIN Nhan_vien ON Ca_lam.id_nhan_vien = Nhan_vien.id WHERE 1=1";
$params = [];
$types = "";

if ($keyword !== '') {
  $sql .= " AND Nhan_vien.ten LIKE ?";
  $params[] = "%{$keyword}%";
  $types .= "s";
}

if ($date_from !== '') {
  $sql .= " AND Ca_lam.ngay_lam >= ?";
  $params[] = $date_from;
  $types .= "s";
}

if ($date_to !== '') {
  $sql .= " AND Ca_lam.ngay_lam <= ?";
  $params[] = $date_to;
  $types .= "s";
}

if ($tang_ca !== '') {
  $sql .= " AND Ca_lam.tang_ca = ?";
  $params[] = (int) $tang_ca;
  $types .= "i";
}

$sql .= " ORDER BY Ca_lam.ngay_lam DESC";

//$sql = "SELECT * FROM Ca_lam WHERE ngay_lam >= ? AND tang_ca = ?";
// $params = ["2024-01-01", 1];
// $types = "si";

if ($params) {
  $stmt = $mysqli->prepare($sql);
  $stmt->bind_param($types, ...$params);
  $stmt->execute();
  $shifts = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
  $stmt->close();
} else {
  $shifts = $mysqli->query($sql)->fetch_all(MYSQLI_ASSOC);
}

include '../includes/header.php';
?>

<div class="page-heading">
  <div>
    <h1>Ca làm việc</h1>
    <p>Theo dõi lịch làm, giờ làm và ca tăng cường.</p>
  </div>
  <a href="them.php" class="btn btn-primary">Thêm ca làm</a>
</div>

<div class="filters-card">
  <form method="GET" class="filters-form">
    <div class="form-group">
      <label>Tìm kiếm</label>
      <input type="text" name="q" class="form-control" value="<?= htmlspecialchars($keyword) ?>"
        placeholder="Tên nhân viên">
    </div>
    <div class="form-group">
      <label>Từ ngày</label>
      <input type="date" name="date_from" class="form-control" value="<?= htmlspecialchars($date_from) ?>">
    </div>
    <div class="form-group">
      <label>Đến ngày</label>
      <input type="date" name="date_to" class="form-control" value="<?= htmlspecialchars($date_to) ?>">
    </div>
    <div class="form-group">
      <label>Loại ca</label>
      <select name="tang_ca" class="form-control">
        <option value="">Tất cả</option>
        <option value="1" <?= $tang_ca === '1' ? 'selected' : '' ?>>Tăng ca</option>
        <option value="0" <?= $tang_ca === '0' ? 'selected' : '' ?>>Thường</option>
      </select>
    </div>
    <button type="submit" class="btn btn-primary">Tìm kiếm</button>
    <!-- form sẽ gửi dữ liệu lên URL bằng phương thức GET. -->
  </form>
</div>

<div class="card">
  <div class="card-header">
    <h2>Danh sách ca làm</h2>
  </div>
  <div class="table-container">
    <table>
      <thead>
        <tr>
          <th>Nhân viên</th>
          <th>Ngày làm</th>
          <th>Giờ</th>
          <th>Hệ số</th>
          <th>Tăng ca</th>
          <th>Thao tác</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($shifts as $s): ?>
          <tr>
            <td><?= htmlspecialchars($s['ten']) ?></td>
            <td><?= $s['ngay_lam'] ?></td>
            <td><?= $s['gio_bat_dau'] ?> - <?= $s['gio_ket_thuc'] ?></td>
            <td><?= $s['he_so'] ?></td>
            <td>
              <span class="badge <?= $s['tang_ca'] ? 'badge-warning' : 'badge-info' ?>">
                <?= $s['tang_ca'] ? 'Tăng ca' : 'Thường' ?>
              </span>
            </td>
            <td>
              <div class="actions-row">
                <a href="sua.php?id=<?= $s['id'] ?>" class="btn btn-success">Sửa</a>
                <a href="xoa.php?id=<?= $s['id'] ?>" class="btn btn-danger"
                  onclick="return confirm('Xóa ca làm này?')">Xóa</a>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (empty($shifts)): ?>
          <tr>
            <td colspan="6" class="empty-state">Không tìm thấy ca làm phù hợp.</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include '../includes/footer.php'; ?>