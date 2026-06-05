<?php
require_once '../includes/db_connect.php';
require_once '../includes/form_helpers.php';

$error = null;
$section_options = merged_text_options(
  distinct_text_values($mysqli, 'Nhan_vien', 'phong_ban'),
  distinct_text_values($mysqli, 'Du_an', 'ten_phong_ban')
);
$manager_options = manager_options($mysqli);

$form_data = [
  'ten_du_an' => '',
  'mo_ta' => '',
  'ngay_bat_dau' => '',
  'ngay_ket_thuc' => '',
  'ten_phong_ban' => '',
  'id_nhan_vien' => '',
];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $form_data = [
    'ten_du_an' => trim($_POST['ten_du_an'] ?? ''),
    'mo_ta' => trim($_POST['mo_ta'] ?? ''),
    'ngay_bat_dau' => trim($_POST['ngay_bat_dau'] ?? ''),
    'ngay_ket_thuc' => trim($_POST['ngay_ket_thuc'] ?? ''),
    'ten_phong_ban' => trim($_POST['ten_phong_ban'] ?? ''),
    'id_nhan_vien' => trim($_POST['id_nhan_vien'] ?? ''),
  ];

  $error = validate_date_range($form_data['ngay_bat_dau'], $form_data['ngay_ket_thuc']);

  if ($error === null) {
    $ten_du_an = $form_data['ten_du_an'];
    $mo_ta = $form_data['mo_ta'];
    $ngay_bat_dau = $form_data['ngay_bat_dau'] !== '' ? $form_data['ngay_bat_dau'] : null;
    $ngay_ket_thuc = $form_data['ngay_ket_thuc'] !== '' ? $form_data['ngay_ket_thuc'] : null;
    $ten_phong_ban = $form_data['ten_phong_ban'];
    $nv_id = $form_data['id_nhan_vien'] !== '' ? (int) $form_data['id_nhan_vien'] : null;

    $stmt = $mysqli->prepare("INSERT INTO Du_an (ten_du_an, mo_ta, ngay_bat_dau, ngay_ket_thuc, ten_phong_ban, id_nhan_vien) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param(
      "sssssi",
      $ten_du_an,
      $mo_ta,
      $ngay_bat_dau,
      $ngay_ket_thuc,
      $ten_phong_ban,
      $nv_id
    );

    if ($stmt->execute()) {
      $stmt->close();
      header("Location: index.php");
      exit();
    }

    $error = 'Không thể lưu dự án: ' . $stmt->error;
    $stmt->close();
  }
}

include '../includes/header.php';
?>

<div class="page-heading">
  <div>
    <h1>Thêm dự án</h1>
    <p>Tạo dự án mới và gán người phụ trách chính.</p>
  </div>
</div>

<div class="card">
  <?php if ($error): ?>
    <div class="form-alert"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <form method="POST">
    <div class="form-grid-3">
      <div class="form-group">
        <label>Tên dự án</label>
        <input type="text" name="ten_du_an" class="form-control" value="<?= htmlspecialchars($form_data['ten_du_an']) ?>" required>
      </div>
      <div class="form-group">
        <label>Phòng ban</label>
        <select name="ten_phong_ban" class="form-control">
          <option value="">-- Chọn phòng ban --</option>
          <?php foreach ($section_options as $section_option): ?>
            <option value="<?= htmlspecialchars($section_option) ?>" <?= $form_data['ten_phong_ban'] === $section_option ? 'selected' : '' ?>>
              <?= htmlspecialchars($section_option) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group">
        <label>Người phụ trách</label>
        <select name="id_nhan_vien" class="form-control">
          <option value="">-- Chọn quản lý --</option>
          <?php foreach ($manager_options as $manager_option): ?>
            <option value="<?= $manager_option['id'] ?>" <?= $form_data['id_nhan_vien'] === (string) $manager_option['id'] ? 'selected' : '' ?>>
              <?= htmlspecialchars($manager_option['ten']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group">
        <label>Ngày bắt đầu</label>
        <input type="date" name="ngay_bat_dau" class="form-control" value="<?= htmlspecialchars($form_data['ngay_bat_dau']) ?>">
      </div>
      <div class="form-group">
        <label>Ngày kết thúc</label>
        <input type="date" name="ngay_ket_thuc" class="form-control" value="<?= htmlspecialchars($form_data['ngay_ket_thuc']) ?>">
        <div class="form-hint">Nếu có ngày kết thúc, ngày bắt đầu phải sớm hơn.</div>
      </div>
      <div class="form-group span-3">
        <label>Mô tả dự án</label>
        <textarea name="mo_ta" class="form-control" rows="3"><?= htmlspecialchars($form_data['mo_ta']) ?></textarea>
      </div>
    </div>
    <div class="form-actions">
      <button type="submit" class="btn btn-primary">Lưu dự án</button>
      <a href="index.php" class="btn btn-secondary">Hủy</a>
    </div>
  </form>
</div>

<?php include '../includes/footer.php'; ?>
