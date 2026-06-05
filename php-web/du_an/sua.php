<?php
require_once '../includes/db_connect.php';
require_once '../includes/form_helpers.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$error = null;

$stmt = $mysqli->prepare("SELECT * FROM Du_an WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$project = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$project) {
  include '../includes/header.php';
  echo "<div class='card'>Không tìm thấy dự án.</div>";
  include '../includes/footer.php';
  exit();
}

$section_options = merged_text_options(
  distinct_text_values($mysqli, 'Nhan_vien', 'phong_ban'),
  distinct_text_values($mysqli, 'Du_an', 'ten_phong_ban'),
  [$project['ten_phong_ban']]
);
$manager_options = manager_options($mysqli, $project['id_nhan_vien']);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $project['ten_du_an'] = trim($_POST['ten_du_an'] ?? '');
  $project['mo_ta'] = trim($_POST['mo_ta'] ?? '');
  $project['ngay_bat_dau'] = trim($_POST['ngay_bat_dau'] ?? '');
  $project['ngay_ket_thuc'] = trim($_POST['ngay_ket_thuc'] ?? '');
  $project['ten_phong_ban'] = trim($_POST['ten_phong_ban'] ?? '');
  $project['id_nhan_vien'] = trim($_POST['id_nhan_vien'] ?? '');

  $error = validate_date_range($project['ngay_bat_dau'], $project['ngay_ket_thuc']);

  if ($error === null) {
    $ten_du_an = $project['ten_du_an'];
    $mo_ta = $project['mo_ta'];
    $ngay_bat_dau = $project['ngay_bat_dau'] !== '' ? $project['ngay_bat_dau'] : null;
    $ngay_ket_thuc = $project['ngay_ket_thuc'] !== '' ? $project['ngay_ket_thuc'] : null;
    $ten_phong_ban = $project['ten_phong_ban'];
    $nv_id = $project['id_nhan_vien'] !== '' ? (int) $project['id_nhan_vien'] : null;

    $stmt = $mysqli->prepare("UPDATE Du_an SET ten_du_an=?, mo_ta=?, ngay_bat_dau=?, ngay_ket_thuc=?, ten_phong_ban=?, id_nhan_vien=? WHERE id=?");
    $stmt->bind_param(
      "sssssii",
      $ten_du_an,
      $mo_ta,
      $ngay_bat_dau,
      $ngay_ket_thuc,
      $ten_phong_ban,
      $nv_id,
      $id
    );

    if ($stmt->execute()) {
      $stmt->close();
      header("Location: index.php");
      exit();
    }

    $error = 'Không thể cập nhật dự án: ' . $stmt->error;
    $stmt->close();
  }

  $section_options = merged_text_options(
    distinct_text_values($mysqli, 'Nhan_vien', 'phong_ban'),
    distinct_text_values($mysqli, 'Du_an', 'ten_phong_ban'),
    [$project['ten_phong_ban']]
  );
  $manager_options = manager_options($mysqli, $project['id_nhan_vien']);
}

include '../includes/header.php';
?>

<div class="card">
  <h2>Cập nhật dự án</h2>
  <hr class="divider">

  <?php if ($error): ?>
    <div class="form-alert"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <form method="POST">
    <div class="form-group">
      <label>Tên dự án:</label>
      <input type="text" name="ten_du_an" class="form-control" value="<?= htmlspecialchars($project['ten_du_an']) ?>" required>
    </div>
    <div class="form-group">
      <label>Mô tả dự án:</label>
      <textarea name="mo_ta" class="form-control" rows="3"><?= htmlspecialchars($project['mo_ta']) ?></textarea>
    </div>
    <div class="form-group">
      <label>Phòng ban:</label>
      <select name="ten_phong_ban" class="form-control">
        <option value="">-- Chọn phòng ban --</option>
        <?php foreach ($section_options as $section_option): ?>
          <option value="<?= htmlspecialchars($section_option) ?>" <?= $project['ten_phong_ban'] === $section_option ? 'selected' : '' ?>>
            <?= htmlspecialchars($section_option) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="form-group">
      <label>Người phụ trách chính:</label>
      <select name="id_nhan_vien" class="form-control">
        <option value="">-- Chọn quản lý --</option>
        <?php foreach ($manager_options as $manager_option): ?>
          <option value="<?= $manager_option['id'] ?>" <?= (string) $project['id_nhan_vien'] === (string) $manager_option['id'] ? 'selected' : '' ?>>
            <?= htmlspecialchars($manager_option['ten']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="form-group">
      <label>Ngày bắt đầu:</label>
      <input type="date" name="ngay_bat_dau" class="form-control" value="<?= htmlspecialchars($project['ngay_bat_dau']) ?>">
    </div>
    <div class="form-group">
      <label>Ngày kết thúc:</label>
      <input type="date" name="ngay_ket_thuc" class="form-control" value="<?= htmlspecialchars((string) $project['ngay_ket_thuc']) ?>">
      <div class="form-hint">Nếu có ngày kết thúc, ngày bắt đầu phải sớm hơn.</div>
    </div>

    <div class="form-actions-compact">
      <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
      <a href="index.php" class="btn btn-secondary">Quay lại</a>
    </div>
  </form>
</div>
<?php include '../includes/footer.php'; ?>
