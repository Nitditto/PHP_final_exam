<?php
require_once '../includes/db_connect.php';

$keyword = trim($_GET['q'] ?? '');
$phong_ban = trim($_GET['phong_ban'] ?? '');
$chuc_vu = trim($_GET['chuc_vu'] ?? '');
$truong_ban = $_GET['truong_ban'] ?? '';

$departments = $mysqli->query("SELECT DISTINCT phong_ban FROM Nhan_vien WHERE phong_ban IS NOT NULL AND phong_ban <> '' ORDER BY phong_ban")->fetch_all(MYSQLI_ASSOC);
$positions = $mysqli->query("SELECT DISTINCT chuc_vu FROM Nhan_vien WHERE chuc_vu IS NOT NULL AND chuc_vu <> '' ORDER BY chuc_vu")->fetch_all(MYSQLI_ASSOC);

$sql = "SELECT * FROM Nhan_vien WHERE 1=1";
$params = [];
$types = "";

if ($keyword !== '') {
  $sql .= " AND (ten LIKE ? OR email LIKE ? OR sdt LIKE ?)";
  $term = "%{$keyword}%";
  array_push($params, $term, $term, $term);
  $types .= "sss";
}

if ($phong_ban !== '') {
  $sql .= " AND phong_ban = ?";
  $params[] = $phong_ban;
  $types .= "s";
}

if ($chuc_vu !== '') {
  $sql .= " AND chuc_vu = ?";
  $params[] = $chuc_vu;
  $types .= "s";
}

if ($truong_ban !== '') {
  $sql .= " AND truong_ban = ?";
  $params[] = (int) $truong_ban;
  $types .= "i";
}

$sql .= " ORDER BY id DESC";

if ($params) {
  $stmt = $mysqli->prepare($sql);
  $stmt->bind_param($types, ...$params);
  $stmt->execute();
  $employees = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
  $stmt->close();
} else {
  $employees = $mysqli->query($sql)->fetch_all(MYSQLI_ASSOC);
}

include '../includes/header.php';
?>

<div class="page-heading">
  <div>
    <h1>Nhân viên</h1>
    <p>Quản lý hồ sơ, phòng ban, chức vụ và lương nhân viên.</p>
  </div>
  <a href="them.php" class="btn btn-primary">Thêm nhân viên</a>
</div>

<div class="filters-card">
  <form method="GET" class="filters-form">
    <div class="form-group">
      <label>Tìm kiếm</label>
      <input type="text" name="q" class="form-control" value="<?= htmlspecialchars($keyword) ?>" placeholder="Tên, email hoặc số điện thoại">
    </div>
    <div class="form-group">
      <label>Phòng ban</label>
      <select name="phong_ban" class="form-control">
        <option value="">Tất cả</option>
        <?php foreach ($departments as $department): ?>
          <option value="<?= htmlspecialchars($department['phong_ban']) ?>" <?= $phong_ban === $department['phong_ban'] ? 'selected' : '' ?>>
            <?= htmlspecialchars($department['phong_ban']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="form-group">
      <label>Chức vụ</label>
      <select name="chuc_vu" class="form-control">
        <option value="">Tất cả</option>
        <?php foreach ($positions as $position): ?>
          <option value="<?= htmlspecialchars($position['chuc_vu']) ?>" <?= $chuc_vu === $position['chuc_vu'] ? 'selected' : '' ?>>
            <?= htmlspecialchars($position['chuc_vu']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="form-group">
      <label>Vai trò</label>
      <select name="truong_ban" class="form-control">
        <option value="">Tất cả</option>
        <option value="1" <?= $truong_ban === '1' ? 'selected' : '' ?>>Trưởng ban</option>
        <option value="0" <?= $truong_ban === '0' ? 'selected' : '' ?>>Nhân viên</option>
      </select>
    </div>
    <button type="submit" class="btn btn-primary">Tìm kiếm</button>
  </form>
</div>

<div class="card">
  <div class="card-header">
    <h2>Danh sách nhân viên</h2>
  </div>
  <div class="table-container">
    <table class="employee-table">
      <colgroup>
        <col class="employee-name-col">
        <col class="employee-department-col">
        <col class="employee-position-col">
        <col class="employee-salary-col">
        <col class="employee-actions-col">
      </colgroup>
      <thead>
        <tr>
          <th>Họ tên</th>
          <th>Phòng ban</th>
          <th>Chức vụ</th>
          <th>Mức lương</th>
          <th>Thao tác</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($employees as $row): ?>
          <tr>
            <td>
              <div class="cell-info">
                <a href="chi_tiet.php?id=<?= $row['id'] ?>" class="main-text table-link"><?= htmlspecialchars($row['ten']) ?></a>
                <span class="sub-text"><?= htmlspecialchars($row['email']) ?></span>
              </div>
            </td>
            <td><?= htmlspecialchars($row['phong_ban']) ?></td>
            <td>
              <div class="cell-info">
                <span><?= htmlspecialchars($row['chuc_vu']) ?></span>
                <?php if ($row['truong_ban']): ?>
                  <span class="status-badge leader">Trưởng ban</span>
                <?php endif; ?>
              </div>
            </td>
            <td class="text-primary-strong"><?= number_format((float) $row['luong'], 0, ',', '.') ?> đ</td>
            <td>
              <div class="actions-row">
                <a href="sua.php?id=<?= $row['id'] ?>" class="btn btn-success">Sửa</a>
                <a href="xoa.php?id=<?= $row['id'] ?>" class="btn btn-danger" onclick="return confirm('Bạn chắc chắn muốn xóa nhân viên này?');">Xóa</a>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (empty($employees)): ?>
          <tr><td colspan="5" class="empty-state">Không tìm thấy nhân viên phù hợp.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include '../includes/footer.php'; ?>
