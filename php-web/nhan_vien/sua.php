<?php
require_once '../includes/db_connect.php';
require_once '../includes/form_helpers.php';

$department_suggestions = distinct_text_values($mysqli, 'Nhan_vien', 'phong_ban');
$role_suggestions = distinct_text_values($mysqli, 'Nhan_vien', 'chuc_vu');

include '../includes/header.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $ten = $_POST['ten'];
    $sdt = $_POST['sdt'];
    $email = $_POST['email'];
    $dia_chi = $_POST['dia_chi'];
    $ngay_sinh = !empty($_POST['ngay_sinh']) ? $_POST['ngay_sinh'] : NULL;
    $gioi_tinh = $_POST['gioi_tinh'];
    $phong_ban = $_POST['phong_ban'];
    $chuc_vu = $_POST['chuc_vu'];
    $truong_ban = isset($_POST['truong_ban']) ? 1 : 0;
    $luong = !empty($_POST['luong']) ? $_POST['luong'] : 0;

    $sql = "UPDATE Nhan_vien SET ten=?, sdt=?, email=?, dia_chi=?, ngay_sinh=?, gioi_tinh=?, phong_ban=?, chuc_vu=?, truong_ban=?, luong=? WHERE id=?";
    if ($stmt = $mysqli->prepare($sql)) {
        $stmt->bind_param("ssssssssidi", $ten, $sdt, $email, $dia_chi, $ngay_sinh, $gioi_tinh, $phong_ban, $chuc_vu, $truong_ban, $luong, $id);
        if ($stmt->execute()) {
            echo "<script>window.location.href='index.php';</script>";
            exit();
        }
        $stmt->close();
    }
}

$stmt = $mysqli->prepare("SELECT * FROM Nhan_vien WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$emp = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$emp) {
    echo "<div class='card'>Không tìm thấy nhân viên.</div>";
    exit();
}
?>

<div class="card">
    <h2>Cập nhật nhân viên</h2>
    <form method="POST">
        <div class="form-grid-2">
            <div class="form-group">
                <label>Họ tên:</label>
                <input type="text" name="ten" class="form-control" value="<?= htmlspecialchars($emp['ten']) ?>" required>
            </div>
            <div class="form-group">
                <label>Số điện thoại:</label>
                <input type="text" name="sdt" class="form-control" value="<?= htmlspecialchars($emp['sdt']) ?>">
            </div>
            <div class="form-group">
                <label>Email:</label>
                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($emp['email']) ?>">
            </div>
            <div class="form-group">
                <label>Ngày sinh:</label>
                <input type="date" name="ngay_sinh" class="form-control" value="<?= htmlspecialchars($emp['ngay_sinh']) ?>">
            </div>
            <div class="form-group">
                <label>Giới tính:</label>
                <select name="gioi_tinh" class="form-control">
                    <option value="Nam" <?= $emp['gioi_tinh'] == 'Nam' ? 'selected' : '' ?>>Nam</option>
                    <option value="Nữ" <?= $emp['gioi_tinh'] == 'Nữ' ? 'selected' : '' ?>>Nữ</option>
                    <option value="Khác" <?= $emp['gioi_tinh'] == 'Khác' ? 'selected' : '' ?>>Khác</option>
                </select>
            </div>
            <div class="form-group">
                <label>Phòng ban:</label>
                <input type="text" name="phong_ban" class="form-control" list="department-suggestions" value="<?= htmlspecialchars($emp['phong_ban']) ?>">
            </div>
            <div class="form-group">
                <label>Chức vụ:</label>
                <input type="text" name="chuc_vu" class="form-control" list="role-suggestions" value="<?= htmlspecialchars($emp['chuc_vu']) ?>">
            </div>
            <div class="form-group">
                <label>Mức lương cơ bản (VNĐ):</label>
                <input type="number" name="luong" class="form-control" value="<?= htmlspecialchars($emp['luong']) ?>">
            </div>
        </div>

        <div class="form-group">
            <label>Địa chỉ:</label>
            <textarea name="dia_chi" class="form-control" rows="3"><?= htmlspecialchars($emp['dia_chi']) ?></textarea>
        </div>

        <div class="form-group">
            <label class="inline-checkbox">
                <input type="checkbox" name="truong_ban" value="1" class="checkbox-lg" 
                    <?= $emp['truong_ban'] ? 'checked' : '' ?>>
                Là trưởng ban / quản lý dự án
            </label>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
            <a href="index.php" class="btn btn-secondary">Hủy</a>
        </div>
    </form>
</div>

<datalist id="department-suggestions">
    <?php foreach ($department_suggestions as $department_suggestion): ?>
        <option value="<?= htmlspecialchars($department_suggestion) ?>"></option>
    <?php endforeach; ?>
</datalist>

<datalist id="role-suggestions">
    <?php foreach ($role_suggestions as $role_suggestion): ?>
        <option value="<?= htmlspecialchars($role_suggestion) ?>"></option>
    <?php endforeach; ?>
</datalist>

<?php include '../includes/footer.php'; ?>
