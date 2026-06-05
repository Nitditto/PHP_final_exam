<?php
require_once '../includes/db_connect.php';

$keyword = trim($_GET['q'] ?? '');
$contract_type = trim($_GET['loai_hop_dong'] ?? '');
$status = trim($_GET['trang_thai'] ?? '');
$date_from = trim($_GET['date_from'] ?? '');

$types_result = $mysqli->query("SELECT DISTINCT loai_hop_dong FROM Hop_dong_lao_dong WHERE loai_hop_dong IS NOT NULL AND loai_hop_dong <> '' ORDER BY loai_hop_dong");
$contract_types = $types_result ? $types_result->fetch_all(MYSQLI_ASSOC) : [];

$sql = "SELECT Hop_dong_lao_dong.*, Nhan_vien.ten FROM Hop_dong_lao_dong JOIN Nhan_vien ON Hop_dong_lao_dong.id_nhan_vien = Nhan_vien.id WHERE 1=1";
$params = [];
$types = "";

if ($keyword !== '') {
  $sql .= " AND Nhan_vien.ten LIKE ?";
  $params[] = "%{$keyword}%";
  $types .= "s";
}

if ($contract_type !== '') {
  $sql .= " AND Hop_dong_lao_dong.loai_hop_dong = ?";
  $params[] = $contract_type;
  $types .= "s";
}

if ($status !== '') {
  $sql .= " AND Hop_dong_lao_dong.trang_thai = ?";
  $params[] = $status;
  $types .= "s";
}

if ($date_from !== '') {
  $sql .= " AND Hop_dong_lao_dong.ngay_bat_dau >= ?";
  $params[] = $date_from;
  $types .= "s";
}

$sql .= " ORDER BY Hop_dong_lao_dong.id DESC";

if ($params) {
  $stmt = $mysqli->prepare($sql);
  $stmt->bind_param($types, ...$params);
  $stmt->execute();
  $contracts = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
  $stmt->close();
} else {
  $contracts = $mysqli->query($sql)->fetch_all(MYSQLI_ASSOC);
}

include '../includes/header.php';
?>

<div class="page-heading">
  <div>
    <h1>Hợp đồng</h1>
    <p>Quản lý hợp đồng lao động, thời hạn và trạng thái hiệu lực.</p>
  </div>
  <a href="them.php" class="btn btn-primary">Thêm hợp đồng</a>
</div>

<div class="filters-card">
  <form method="GET" class="filters-form">
    <div class="form-group">
      <label>Tìm kiếm</label>
      <input type="text" name="q" class="form-control" value="<?= htmlspecialchars($keyword) ?>" placeholder="Tên nhân viên">
    </div>
    <div class="form-group">
      <label>Loại hợp đồng</label>
      <select name="loai_hop_dong" class="form-control">
        <option value="">Tất cả</option>
        <?php foreach ($contract_types as $type): ?>
          <option value="<?= htmlspecialchars($type['loai_hop_dong']) ?>" <?= $contract_type === $type['loai_hop_dong'] ? 'selected' : '' ?>>
            <?= htmlspecialchars($type['loai_hop_dong']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="form-group">
      <label>Trạng thái</label>
      <select name="trang_thai" class="form-control">
        <option value="">Tất cả</option>
        <option value="Đang hiệu lực" <?= $status === 'Đang hiệu lực' ? 'selected' : '' ?>>Đang hiệu lực</option>
        <option value="Đã hết hạn" <?= $status === 'Đã hết hạn' ? 'selected' : '' ?>>Đã hết hạn</option>
        <option value="Đã thanh lý" <?= $status === 'Đã thanh lý' ? 'selected' : '' ?>>Đã thanh lý</option>
      </select>
    </div>
    <div class="form-group">
      <label>Bắt đầu từ</label>
      <input type="date" name="date_from" class="form-control" value="<?= htmlspecialchars($date_from) ?>">
    </div>
    <button type="submit" class="btn btn-primary">Tìm kiếm</button>
  </form>
</div>

<div class="card">
  <div class="card-header">
    <h2>Danh sách hợp đồng lao động</h2>
  </div>
  <div class="table-container">
    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>Nhân viên</th>
          <th>Loại HĐ</th>
          <th>Thời hạn</th>
          <th>Lương cơ bản</th>
          <th>Trạng thái</th>
          <th>Thao tác</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($contracts as $c): ?>
          <tr>
            <td><?= $c['id'] ?></td>
            <td><div class="text-strong"><?= htmlspecialchars($c['ten']) ?></div></td>
            <td><?= htmlspecialchars($c['loai_hop_dong']) ?></td>
            <td>
              <div class="text-small"><?= $c['ngay_bat_dau'] ?></div>
              <div class="text-small-muted">đến <?= $c['ngay_ket_thuc'] ?: 'Không thời hạn' ?></div>
            </td>
            <td class="text-primary-strong"><?= number_format((float) $c['luong_co_ban']) ?> đ</td>
            <td>
              <span class="badge <?= $c['trang_thai'] == 'Đang hiệu lực' ? 'badge-success' : 'badge-danger' ?>">
                <?= htmlspecialchars($c['trang_thai']) ?>
              </span>
            </td>
            <td>
              <div class="actions-row">
                <a href="sua.php?id=<?= $c['id'] ?>" class="btn btn-success">Sửa</a>
                <a href="xoa.php?id=<?= $c['id'] ?>" class="btn btn-danger" onclick="return confirm('Xóa hợp đồng này?')">Xóa</a>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (empty($contracts)): ?>
          <tr><td colspan="7" class="empty-state">Không tìm thấy hợp đồng phù hợp.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include '../includes/footer.php'; ?>
