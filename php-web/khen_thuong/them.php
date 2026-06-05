<?php
require_once '../includes/db_connect.php';
require_once '../includes/form_helpers.php';

$error = null;
$award_types = award_type_options();
$form_data = [
  'id_nhan_vien' => '',
  'loai_khen_thuong' => $award_types[0],
  'ngay_ghi_nhan' => '',
  'so_tien' => '0',
  'ly_do' => '',
];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $form_data = [
    'id_nhan_vien' => trim($_POST['id_nhan_vien'] ?? ''),
    'loai_khen_thuong' => trim($_POST['loai_khen_thuong'] ?? ''),
    'ngay_ghi_nhan' => trim($_POST['ngay_ghi_nhan'] ?? ''),
    'so_tien' => trim($_POST['so_tien'] ?? '0'),
    'ly_do' => trim($_POST['ly_do'] ?? ''),
  ];

  $loai_khen_thuong = $form_data['loai_khen_thuong'];
  $ngay_ghi_nhan = $form_data['ngay_ghi_nhan'];
  $ly_do = $form_data['ly_do'];
  $stmt = $mysqli->prepare("INSERT INTO Danh_gia_khen_thuong (id_nhan_vien, loai_khen_thuong, ngay_ghi_nhan, so_tien, ly_do) VALUES (?, ?, ?, ?, ?)");
  $nv_id = (int) $form_data['id_nhan_vien'];
  $so_tien = (float) $form_data['so_tien'];
  $stmt->bind_param("issds", $nv_id, $loai_khen_thuong, $ngay_ghi_nhan, $so_tien, $ly_do);

  if ($stmt->execute()) {
    $stmt->close();
    header("Location: index.php");
    exit();
  }

  $error = 'Không thể lưu ghi nhận: ' . $stmt->error;
  $stmt->close();
}

include '../includes/header.php';
?>

<div class="page-heading">
  <div>
    <h1>Thêm ghi nhận</h1>
    <p>Ghi nhận khen thưởng hoặc kỷ luật cho nhân viên.</p>
  </div>
</div>

<div class="card">
  <?php if ($error): ?>
    <div class="form-alert"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <form method="POST">
    <div class="form-grid-2">
      <div class="form-group">
        <label>Nhân viên</label>
        <select name="id_nhan_vien" class="form-control" required>
          <option value="">Chọn nhân viên</option>
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
        <label>Loại</label>
        <select name="loai_khen_thuong" class="form-control" required>
          <?php foreach ($award_types as $award_type): ?>
            <option value="<?= htmlspecialchars($award_type) ?>" <?= $form_data['loai_khen_thuong'] === $award_type ? 'selected' : '' ?>>
              <?= htmlspecialchars($award_type) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group">
        <label>Số tiền</label>
        <input type="number" name="so_tien" class="form-control" value="<?= htmlspecialchars($form_data['so_tien']) ?>">
      </div>
      <div class="form-group">
        <label>Ngày ghi nhận</label>
        <input type="date" name="ngay_ghi_nhan" class="form-control" value="<?= htmlspecialchars($form_data['ngay_ghi_nhan']) ?>" required>
      </div>
      <div class="form-group span-3">
        <label>Lý do</label>
        <textarea name="ly_do" class="form-control" rows="3"><?= htmlspecialchars($form_data['ly_do']) ?></textarea>
      </div>
    </div>
    <div class="form-actions">
      <button type="submit" class="btn btn-primary">Lưu ghi nhận</button>
      <a href="index.php" class="btn btn-secondary">Hủy</a>
    </div>
  </form>
</div>

<?php include '../includes/footer.php'; ?>
