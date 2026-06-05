<?php
require_once '../includes/db_connect.php';

$keyword = trim($_GET['q'] ?? '');
$type = trim($_GET['loai'] ?? '');
$date_from = trim($_GET['date_from'] ?? '');
$date_to = trim($_GET['date_to'] ?? '');

$types_result = $mysqli->query("SELECT DISTINCT loai_khen_thuong FROM Danh_gia_khen_thuong WHERE loai_khen_thuong IS NOT NULL AND loai_khen_thuong <> '' ORDER BY loai_khen_thuong");
$award_types = $types_result ? $types_result->fetch_all(MYSQLI_ASSOC) : [];

$sql = "SELECT Danh_gia_khen_thuong.*, Nhan_vien.ten FROM Danh_gia_khen_thuong JOIN Nhan_vien ON Danh_gia_khen_thuong.id_nhan_vien = Nhan_vien.id WHERE 1=1";
$params = [];
$types = "";

if ($keyword !== '') {
  $sql .= " AND (Nhan_vien.ten LIKE ? OR Danh_gia_khen_thuong.ly_do LIKE ?)";
  $term = "%{$keyword}%";
  array_push($params, $term, $term);
  $types .= "ss";
}

if ($type !== '') {
  $sql .= " AND Danh_gia_khen_thuong.loai_khen_thuong = ?";
  $params[] = $type;
  $types .= "s";
}

if ($date_from !== '') {
  $sql .= " AND Danh_gia_khen_thuong.ngay_ghi_nhan >= ?";
  $params[] = $date_from;
  $types .= "s";
}

if ($date_to !== '') {
  $sql .= " AND Danh_gia_khen_thuong.ngay_ghi_nhan <= ?";
  $params[] = $date_to;
  $types .= "s";
}

$sql .= " ORDER BY Danh_gia_khen_thuong.ngay_ghi_nhan DESC";

if ($params) {
  $stmt = $mysqli->prepare($sql);
  $stmt->bind_param($types, ...$params);
  $stmt->execute();
  $awards = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
  $stmt->close();
} else {
  $awards = $mysqli->query($sql)->fetch_all(MYSQLI_ASSOC);
}

include '../includes/header.php';
?>

<div class="page-heading">
  <div>
    <h1>Khen thưởng</h1>
    <p>Ghi nhận khen thưởng, kỷ luật và giá trị liên quan.</p>
  </div>
  <a href="them.php" class="btn btn-primary">Thêm ghi nhận</a>
</div>

<div class="filters-card">
  <form method="GET" class="filters-form">
    <div class="form-group">
      <label>Tìm kiếm</label>
      <input type="text" name="q" class="form-control" value="<?= htmlspecialchars($keyword) ?>" placeholder="Tên nhân viên hoặc lý do">
    </div>
    <div class="form-group">
      <label>Loại</label>
      <select name="loai" class="form-control">
        <option value="">Tất cả</option>
        <?php foreach ($award_types as $award_type): ?>
          <option value="<?= htmlspecialchars($award_type['loai_khen_thuong']) ?>" <?= $type === $award_type['loai_khen_thuong'] ? 'selected' : '' ?>>
            <?= htmlspecialchars($award_type['loai_khen_thuong']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="form-group">
      <label>Từ ngày</label>
      <input type="date" name="date_from" class="form-control" value="<?= htmlspecialchars($date_from) ?>">
    </div>
    <div class="form-group">
      <label>Đến ngày</label>
      <input type="date" name="date_to" class="form-control" value="<?= htmlspecialchars($date_to) ?>">
    </div>
    <button type="submit" class="btn btn-primary">Tìm kiếm</button>
  </form>
</div>

<div class="card">
  <div class="card-header">
    <h2>Danh sách khen thưởng / kỷ luật</h2>
  </div>
  <div class="table-container">
    <table>
      <thead>
        <tr>
          <th>Nhân viên</th>
          <th>Loại</th>
          <th>Số tiền</th>
          <th>Ngày</th>
          <th>Lý do</th>
          <th>Thao tác</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($awards as $a): ?>
          <tr>
            <td><div class="text-strong"><?= htmlspecialchars($a['ten']) ?></div></td>
            <td><?= htmlspecialchars($a['loai_khen_thuong']) ?></td>
            <td class="text-primary-strong"><?= number_format((float) $a['so_tien']) ?> đ</td>
            <td><?= $a['ngay_ghi_nhan'] ?></td>
            <td title="<?= htmlspecialchars($a['ly_do']) ?>"><?= htmlspecialchars($a['ly_do']) ?></td>
            <td>
              <div class="actions-row">
                <a href="sua.php?id=<?= $a['id'] ?>" class="btn btn-success">Sửa</a>
                <a href="xoa.php?id=<?= $a['id'] ?>" class="btn btn-danger" onclick="return confirm('Xóa dữ liệu này?')">Xóa</a>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (empty($awards)): ?>
          <tr><td colspan="6" class="empty-state">Không tìm thấy ghi nhận phù hợp.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include '../includes/footer.php'; ?>
