<?php
require_once '../includes/db_connect.php';
require_once '../includes/form_helpers.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$error = null;

$stmt = $mysqli->prepare("SELECT * FROM Danh_gia_khen_thuong WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$award = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$award) {
  include '../includes/header.php';
  echo "<div class='card'>Không tìm thấy thông tin khen thưởng.</div>";
  include '../includes/footer.php';
  exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $award['id_nhan_vien'] = trim($_POST['id_nhan_vien'] ?? '');
  $award['loai_khen_thuong'] = trim($_POST['loai_khen_thuong'] ?? '');
  $award['ngay_ghi_nhan'] = trim($_POST['ngay_ghi_nhan'] ?? '');
  $award['so_tien'] = trim($_POST['so_tien'] ?? '0');
  $award['ly_do'] = trim($_POST['ly_do'] ?? '');

  $loai_khen_thuong = $award['loai_khen_thuong'];
  $ngay_ghi_nhan = $award['ngay_ghi_nhan'];
  $ly_do = $award['ly_do'];
  $stmt = $mysqli->prepare("UPDATE Danh_gia_khen_thuong SET id_nhan_vien=?, loai_khen_thuong=?, ngay_ghi_nhan=?, so_tien=?, ly_do=? WHERE id=?");
  $nv_id = (int) $award['id_nhan_vien'];
  $so_tien = (float) $award['so_tien'];
  $stmt->bind_param("issdsi", $nv_id, $loai_khen_thuong, $ngay_ghi_nhan, $so_tien, $ly_do, $id);

  if ($stmt->execute()) {
    $stmt->close();
    header("Location: index.php");
    exit();
  }

  $error = 'Không thể cập nhật khen thưởng: ' . $stmt->error;
  $stmt->close();
}

$award_types = award_type_options_with_selected($award['loai_khen_thuong']);

include '../includes/header.php';
?>

<div class="card">
  <h2>Sửa thông tin khen thưởng</h2>
  <hr class="divider">

  <?php if ($error): ?>
    <div class="form-alert"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <form method="POST">
    <div class="form-group">
      <label>Nhân viên:</label>
      <select name="id_nhan_vien" class="form-control" required>
        <?php
        $nvs = $mysqli->query("SELECT id, ten FROM Nhan_vien ORDER BY ten");
        while ($nv = $nvs->fetch_assoc()) {
          $selected = (string) $award['id_nhan_vien'] === (string) $nv['id'] ? 'selected' : '';
          echo "<option value='{$nv['id']}' {$selected}>" . htmlspecialchars($nv['ten']) . "</option>";
        }
        ?>
      </select>
    </div>
    <div class="form-group">
      <label>Loại khen thưởng:</label>
      <select name="loai_khen_thuong" class="form-control" required>
        <?php foreach ($award_types as $award_type): ?>
          <option value="<?= htmlspecialchars($award_type) ?>" <?= $award['loai_khen_thuong'] === $award_type ? 'selected' : '' ?>>
            <?= htmlspecialchars($award_type) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="form-group">
      <label>Số tiền thưởng (VNĐ):</label>
      <input type="number" name="so_tien" class="form-control" value="<?= htmlspecialchars((string) $award['so_tien']) ?>">
    </div>
    <div class="form-group">
      <label>Ngày ghi nhận:</label>
      <input type="date" name="ngay_ghi_nhan" class="form-control" value="<?= htmlspecialchars($award['ngay_ghi_nhan']) ?>" required>
    </div>
    <div class="form-group">
      <label>Lý do / Ghi chú:</label>
      <textarea name="ly_do" class="form-control" rows="3"><?= htmlspecialchars($award['ly_do']) ?></textarea>
    </div>

    <div class="form-actions-compact">
      <button type="submit" class="btn btn-primary">Lưu cập nhật</button>
      <a href="index.php" class="btn btn-secondary">Hủy</a>
    </div>
  </form>
</div>
<?php include '../includes/footer.php'; ?>
