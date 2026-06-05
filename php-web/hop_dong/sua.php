<?php
require_once '../includes/db_connect.php';
require_once '../includes/form_helpers.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$error = null;

$stmt = $mysqli->prepare("SELECT * FROM Hop_dong_lao_dong WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$contract = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$contract) {
  include '../includes/header.php';
  echo "<div class='card'>Không tìm thấy hợp đồng.</div>";
  include '../includes/footer.php';
  exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $contract['id_nhan_vien'] = trim($_POST['id_nhan_vien'] ?? '');
  $contract['loai_hop_dong'] = trim($_POST['loai_hop_dong'] ?? '');
  $contract['ngay_bat_dau'] = trim($_POST['ngay_bat_dau'] ?? '');
  $contract['ngay_ket_thuc'] = trim($_POST['ngay_ket_thuc'] ?? '');
  $contract['luong_co_ban'] = trim($_POST['luong_co_ban'] ?? '');
  $contract['trang_thai'] = trim($_POST['trang_thai'] ?? '');

  $error = validate_date_range($contract['ngay_bat_dau'], $contract['ngay_ket_thuc']);

  if ($error === null) {
    $loai_hop_dong = $contract['loai_hop_dong'];
    $ngay_bat_dau = $contract['ngay_bat_dau'];
    $ngay_ket_thuc = $contract['ngay_ket_thuc'] !== '' ? $contract['ngay_ket_thuc'] : null;
    $nv_id = (int) $contract['id_nhan_vien'];
    $luong = (float) $contract['luong_co_ban'];
    $trang_thai = $contract['trang_thai'];

    $stmt = $mysqli->prepare("UPDATE Hop_dong_lao_dong SET id_nhan_vien=?, loai_hop_dong=?, ngay_bat_dau=?, ngay_ket_thuc=?, luong_co_ban=?, trang_thai=? WHERE id=?");
    $stmt->bind_param("isssdsi", $nv_id, $loai_hop_dong, $ngay_bat_dau, $ngay_ket_thuc, $luong, $trang_thai, $id);

    if ($stmt->execute()) {
      $stmt->close();
      header("Location: index.php");
      exit();
    }

    $error = 'Không thể cập nhật hợp đồng: ' . $stmt->error;
    $stmt->close();
  }
}

include '../includes/header.php';
?>

<div class="card">
  <h2>Cập nhật hợp đồng lao động</h2>
  <hr class="divider">

  <?php if ($error): ?>
    <div class="form-alert"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <form method="POST">
    <div class="form-group">
      <label>Nhân viên ký kết:</label>
      <select name="id_nhan_vien" class="form-control" required>
        <?php
        $nvs = $mysqli->query("SELECT id, ten FROM Nhan_vien ORDER BY ten");
        while ($nv = $nvs->fetch_assoc()) {
          $selected = (string) $contract['id_nhan_vien'] === (string) $nv['id'] ? 'selected' : '';
          echo "<option value='{$nv['id']}' {$selected}>" . htmlspecialchars($nv['ten']) . "</option>";
        }
        ?>
      </select>
    </div>
    <div class="form-group">
      <label>Loại hợp đồng:</label>
      <select name="loai_hop_dong" class="form-control">
        <?php
        $contract_types = ['Thử việc', 'Chính thức - 1 năm', 'Vô thời hạn'];
        if ($contract['loai_hop_dong'] !== '' && !in_array($contract['loai_hop_dong'], $contract_types, true)) {
          $contract_types[] = $contract['loai_hop_dong'];
        }
        foreach ($contract_types as $contract_type) {
          $selected = $contract['loai_hop_dong'] === $contract_type ? 'selected' : '';
          echo "<option value='" . htmlspecialchars($contract_type) . "' {$selected}>" . htmlspecialchars($contract_type) . "</option>";
        }
        ?>
      </select>
    </div>
    <div class="form-group">
      <label>Lương cơ bản (VNĐ):</label>
      <input type="number" name="luong_co_ban" class="form-control" value="<?= htmlspecialchars((string) $contract['luong_co_ban']) ?>">
    </div>
    <div class="form-group">
      <label>Ngày bắt đầu:</label>
      <input type="date" name="ngay_bat_dau" class="form-control" value="<?= htmlspecialchars($contract['ngay_bat_dau']) ?>" required>
    </div>
    <div class="form-group">
      <label>Ngày kết thúc:</label>
      <input type="date" name="ngay_ket_thuc" class="form-control" value="<?= htmlspecialchars((string) $contract['ngay_ket_thuc']) ?>">
      <div class="form-hint">Nếu có ngày kết thúc, ngày bắt đầu phải sớm hơn.</div>
    </div>
    <div class="form-group">
      <label>Trạng thái:</label>
      <select name="trang_thai" class="form-control">
        <?php
        $contract_statuses = ['Đang hiệu lực', 'Đã hết hạn', 'Đã thanh lý'];
        foreach ($contract_statuses as $contract_status) {
          $selected = $contract['trang_thai'] === $contract_status ? 'selected' : '';
          echo "<option value='" . htmlspecialchars($contract_status) . "' {$selected}>" . htmlspecialchars($contract_status) . "</option>";
        }
        ?>
      </select>
    </div>
    <div class="form-actions-compact">
      <button type="submit" class="btn btn-primary">Cập nhật hợp đồng</button>
      <a href="index.php" class="btn btn-secondary">Hủy</a>
    </div>
  </form>
</div>
<?php include '../includes/footer.php'; ?>
