<?php
require_once '../includes/db_connect.php';
include '../includes/header.php';

// take id from url
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $nv_id = $_POST['id_nhan_vien'];
  $bd = $_POST['gio_bat_dau'];
  $kt = $_POST['gio_ket_thuc'];
  $ngay = $_POST['ngay_lam'];
  $tang_ca = isset($_POST['tang_ca']) ? 1 : 0;
  $he_so = $tang_ca ? $_POST['he_so'] : 1.0;

  $stmt = $mysqli->prepare("UPDATE Ca_lam SET id_nhan_vien=?, gio_bat_dau=?, gio_ket_thuc=?, ngay_lam=?, he_so=?, tang_ca=? WHERE id=?");
  $stmt->bind_param("isssdii", $nv_id, $bd, $kt, $ngay, $he_so, $tang_ca, $id);
  if ($stmt->execute()) {
    echo "<script>window.location.href='index.php';</script>";
    exit();
  }
  $stmt->close();
}

$stmt = $mysqli->prepare("SELECT * FROM Ca_lam WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$shift = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$shift) {
  echo "<div class='card'>Không tìm thấy ca làm việc.</div>";
  exit();
}
?>

<div class="card">
  <h2>Cập nhật ca làm việc</h2>
  <hr class="divider">
  <form method="POST">
    <div class="form-group">
      <label>Nhân viên:</label>
      <select name="id_nhan_vien" class="form-control" required>
        <?php
        $nvs = $mysqli->query("SELECT id, ten FROM Nhan_vien");
        while ($nv = $nvs->fetch_assoc()) {
          $selected = ($nv['id'] == $shift['id_nhan_vien']) ? 'selected' : '';
          echo "<option value='{$nv['id']}' $selected>{$nv['ten']}</option>";
        }
        ?>
      </select>
    </div>
    <div class="form-group"><label>Ngày làm:</label><input type="date" name="ngay_lam" class="form-control" value="<?= $shift['ngay_lam'] ?>" required></div>
    <div class="form-group"><label>Giờ bắt đầu:</label><input type="time" name="gio_bat_dau" class="form-control" value="<?= $shift['gio_bat_dau'] ?>" required></div>
    <div class="form-group"><label>Giờ kết thúc:</label><input type="time" name="gio_ket_thuc" class="form-control" value="<?= $shift['gio_ket_thuc'] ?>" required></div>

    <div class="form-group">
      <label class="checkbox-label-wide">
        <input type="checkbox" name="tang_ca" id="chkTangCaEdit" class="checkbox-md"
          onchange="toggleHeSoEdit()" <?= $shift['tang_ca'] ? 'checked' : '' ?>>
        Là Ca Tăng cường / Tăng ca
      </label>
    </div>

    <div class="form-group">
      <label>Hệ số lương:</label>
      <input type="number" step="0.1" name="he_so" id="txtHeSoEdit" class="form-control" value="<?= $shift['he_so'] ?>" <?= $shift['tang_ca'] ? '' : 'disabled' ?>>
    </div>

    <div class="form-actions-compact">
      <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
      <a href="index.php" class="btn btn-secondary">Hủy</a>
    </div>
  </form>
</div>

<script>
  function toggleHeSoEdit() {
    var checkBox = document.getElementById("chkTangCaEdit");
    var heSoInput = document.getElementById("txtHeSoEdit");
    if (checkBox.checked == true) {
      heSoInput.disabled = false;
    } else {
      heSoInput.disabled = true;
      heSoInput.value = "1.0";
    }
  }
</script>

<?php include '../includes/footer.php'; ?>
