<?php
require_once '../includes/db_connect.php';
require_once '../includes/form_helpers.php';

$error = null;
$form_data = [
  'id_nhan_vien' => '',
  'loai_hop_dong' => 'Thử việc',
  'ngay_bat_dau' => '',
  'ngay_ket_thuc' => '',
  'luong_co_ban' => '',
  'trang_thai' => 'Đang hiệu lực',
];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $form_data = [
    'id_nhan_vien' => trim($_POST['id_nhan_vien'] ?? ''),
    'loai_hop_dong' => trim($_POST['loai_hop_dong'] ?? ''),
    'ngay_bat_dau' => trim($_POST['ngay_bat_dau'] ?? ''),
    'ngay_ket_thuc' => trim($_POST['ngay_ket_thuc'] ?? ''),
    'luong_co_ban' => trim($_POST['luong_co_ban'] ?? ''),
    'trang_thai' => trim($_POST['trang_thai'] ?? ''),
  ];

  $error = validate_date_range($form_data['ngay_bat_dau'], $form_data['ngay_ket_thuc']);

  if ($error === null) {
    $loai_hop_dong = $form_data['loai_hop_dong'];
    $ngay_bat_dau = $form_data['ngay_bat_dau'];
    $ngay_ket_thuc = $form_data['ngay_ket_thuc'] !== '' ? $form_data['ngay_ket_thuc'] : null;
    $nv_id = (int) $form_data['id_nhan_vien'];
    $luong = (float) $form_data['luong_co_ban'];
    $trang_thai = $form_data['trang_thai'];

    $stmt = $mysqli->prepare("INSERT INTO Hop_dong_lao_dong (id_nhan_vien, loai_hop_dong, ngay_bat_dau, ngay_ket_thuc, luong_co_ban, trang_thai) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssds", $nv_id, $loai_hop_dong, $ngay_bat_dau, $ngay_ket_thuc, $luong, $trang_thai);

    if ($stmt->execute()) {
      $stmt->close();
      header("Location: index.php");
      exit();
    }

    $error = 'Không thể tạo hợp đồng: ' . $stmt->error;
    $stmt->close();
  }
}

include '../includes/header.php';
?>

<div class="page-heading">
  <div>
    <h1>Thêm hợp đồng</h1>
    <p>Ký hợp đồng lao động mới cho nhân viên.</p>
  </div>
</div>

<div class="card">
  <?php if ($error): ?>
    <div class="form-alert"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <form method="POST">
    <div class="form-grid-3">
      <div class="form-group">
        <label>Nhân viên ký kết</label>
        <select name="id_nhan_vien" class="form-control" required>
          <option value="">-- Chọn nhân viên --</option>
          <?php
          $nvs = $mysqli->query("SELECT id, ten FROM Nhan_vien ORDER BY ten");
          while ($nv = $nvs->fetch_assoc()) {
            $selected = $form_data['id_nhan_vien'] === (string) $nv['id'] ? 'selected' : '';
            echo "<option value='{$nv['id']}' {$selected}>" . htmlspecialchars($nv['ten']) . "</option>";
          }
          ?>
        </select>
      </div>
      <div class="form-group">
        <label>Loại hợp đồng</label>
        <select name="loai_hop_dong" class="form-control">
          <?php
          $contract_types = ['Thử việc', 'Chính thức - 1 năm', 'Vô thời hạn'];
          foreach ($contract_types as $contract_type) {
            $selected = $form_data['loai_hop_dong'] === $contract_type ? 'selected' : '';
            echo "<option value='" . htmlspecialchars($contract_type) . "' {$selected}>" . htmlspecialchars($contract_type) . "</option>";
          }
          ?>
        </select>
      </div>
      <div class="form-group">
        <label>Lương cơ bản</label>
        <input type="number" name="luong_co_ban" class="form-control" value="<?= htmlspecialchars($form_data['luong_co_ban']) ?>" required>
      </div>
      <div class="form-group">
        <label>Ngày bắt đầu</label>
        <input type="date" name="ngay_bat_dau" class="form-control" value="<?= htmlspecialchars($form_data['ngay_bat_dau']) ?>" required>
      </div>
      <div class="form-group">
        <label>Ngày kết thúc</label>
        <input type="date" name="ngay_ket_thuc" class="form-control" value="<?= htmlspecialchars($form_data['ngay_ket_thuc']) ?>">
        <div class="form-hint">Nếu có ngày kết thúc, ngày bắt đầu phải sớm hơn.</div>
      </div>
      <div class="form-group">
        <label>Trạng thái</label>
        <select name="trang_thai" class="form-control">
          <?php
          $contract_statuses = ['Đang hiệu lực', 'Đã hết hạn', 'Đã thanh lý'];
          foreach ($contract_statuses as $contract_status) {
            $selected = $form_data['trang_thai'] === $contract_status ? 'selected' : '';
            echo "<option value='" . htmlspecialchars($contract_status) . "' {$selected}>" . htmlspecialchars($contract_status) . "</option>";
          }
          ?>
        </select>
      </div>
    </div>
    <div class="form-actions">
      <button type="submit" class="btn btn-primary">Xác nhận ký kết</button>
      <a href="index.php" class="btn btn-secondary">Hủy</a>
    </div>
  </form>
</div>

<?php include '../includes/footer.php'; ?>
