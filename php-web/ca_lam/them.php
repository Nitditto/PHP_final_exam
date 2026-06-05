<?php
require_once '../includes/db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $nv_id = $_POST['id_nhan_vien'];
  $bd = $_POST['gio_bat_dau'];
  $kt = $_POST['gio_ket_thuc'];
  $ngay = $_POST['ngay_lam'];
  $tang_ca = isset($_POST['tang_ca']) ? 1 : 0;
  $he_so = $tang_ca ? $_POST['he_so'] : 1.0;

  $stmt = $mysqli->prepare("INSERT INTO Ca_lam (id_nhan_vien, gio_bat_dau, gio_ket_thuc, ngay_lam, he_so, tang_ca) VALUES (?, ?, ?, ?, ?, ?)");
  $stmt->bind_param("isssdi", $nv_id, $bd, $kt, $ngay, $he_so, $tang_ca);
  if ($stmt->execute()) {
    header("Location: index.php");
    exit();
  }
  $stmt->close();
}

include '../includes/header.php';
?>

<div class="page-heading">
  <div>
    <h1>Thêm ca làm</h1>
    <p>Phân ca làm việc cho nhân viên.</p>
  </div>
</div>

<div class="card">
  <form method="POST">
    <div class="form-grid-2">
      <div class="form-group">
        <label>Nhân viên</label>
        <select name="id_nhan_vien" class="form-control" required>
          <option value="">Chọn nhân viên</option>
          <?php
          $nvs = $mysqli->query("SELECT id, ten FROM Nhan_vien ORDER BY ten");
          while ($nv = $nvs->fetch_assoc()) {
            echo "<option value='{$nv['id']}'>" . htmlspecialchars($nv['ten']) . "</option>";
          }
          ?>
        </select>
      </div>
      <div class="form-group">
        <label>Ngày làm</label>
        <input type="date" name="ngay_lam" class="form-control" required>
      </div>
      <div class="form-group">
        <label>Giờ bắt đầu</label>
        <input type="time" name="gio_bat_dau" class="form-control" required>
      </div>
      <div class="form-group">
        <label>Giờ kết thúc</label>
        <input type="time" name="gio_ket_thuc" class="form-control" required>
      </div>
      <div class="form-group">
        <label class="checkbox-label-wide">
          <input type="checkbox" name="tang_ca" id="chkTangCa" onchange="toggleHeSo()" class="checkbox-md">
          Tăng ca
        </label>
      </div>
      <div class="form-group">
        <label>Hệ số lương</label>
        <input type="number" step="0.1" name="he_so" id="txtHeSo" class="form-control" value="1.0" disabled>
      </div>
    </div>
    <div class="form-actions">
      <button type="submit" class="btn btn-primary">Thêm ca</button>
      <a href="index.php" class="btn btn-secondary">Hủy</a>
    </div>
  </form>
</div>

<script>
  function toggleHeSo() {
    var checkBox = document.getElementById("chkTangCa");
    var heSoInput = document.getElementById("txtHeSo");
    heSoInput.disabled = !checkBox.checked;
    heSoInput.value = checkBox.checked ? "1.5" : "1.0";
  }
</script>

<?php include '../includes/footer.php'; ?>
