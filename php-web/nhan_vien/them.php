<?php
require_once '../includes/db_connect.php';
require_once '../includes/form_helpers.php';

$department_suggestions = distinct_text_values($mysqli, 'Nhan_vien', 'phong_ban');
$role_suggestions = distinct_text_values($mysqli, 'Nhan_vien', 'chuc_vu');

include '../includes/header.php';

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

    $sql = "INSERT INTO Nhan_vien (ten, sdt, email, dia_chi, ngay_sinh, gioi_tinh, phong_ban, chuc_vu, truong_ban, luong) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    if ($stmt = $mysqli->prepare($sql)) {
        $stmt->bind_param("ssssssssid", $ten, $sdt, $email, $dia_chi, $ngay_sinh, $gioi_tinh, $phong_ban, $chuc_vu, $truong_ban, $luong);
        if ($stmt->execute()) {
            echo "<script>window.location.href='index.php';</script>";
            exit();
        } else {
            echo "<div class='card text-danger'>Lỗi: " . $mysqli->error . "</div>";
        }
        $stmt->close();
    }
}
?>

<div class="card">
    <h2>Thêm nhân viên mới</h2>
    <form method="POST">
        <div class="form-grid-2">
            <div class="form-group">
                <label>Họ tên (*):</label>
                <input type="text" name="ten" class="form-control" required placeholder="Nhập họ và tên">
            </div>
            <div class="form-group">
                <label>Số điện thoại:</label>
                <input type="text" name="sdt" class="form-control" placeholder="090x xxx xxx">
            </div>
            <div class="form-group">
                <label>Email:</label>
                <input type="email" name="email" class="form-control" placeholder="example@gmail.com">
            </div>
            <div class="form-group">
                <label>Ngày sinh:</label>
                <input type="date" name="ngay_sinh" class="form-control">
            </div>
            <div class="form-group">
                <label>Giới tính:</label>
                <select name="gioi_tinh" class="form-control">
                    <option value="Nam">Nam</option>
                    <option value="Nữ">Nữ</option>
                    <option value="Khác">Khác</option>
                </select>
            </div>
            <div class="form-group">
                <label>Phòng ban:</label>
                <input type="text" name="phong_ban" class="form-control" list="department-suggestions" placeholder="Vd: Kế toán, IT...">
            </div>
            <div class="form-group">
                <label>Chức vụ:</label>
                <input type="text" name="chuc_vu" class="form-control" list="role-suggestions" placeholder="Vd: Lập trình viên...">
            </div>
            <div class="form-group">
                <label>Mức lương cơ bản (VNĐ):</label>
                <input type="number" name="luong" class="form-control" value="0">
            </div>
        </div>

        <div class="form-group">
            <label>Địa chỉ:</label>
            <textarea name="dia_chi" class="form-control" rows="3" placeholder="Nhập địa chỉ cư trú"></textarea>
        </div>

        <div class="form-group">
            <label class="inline-checkbox">
                <input type="checkbox" name="truong_ban" value="1" class="checkbox-lg">
                Là trưởng ban / quản lý dự án
            </label>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Lưu nhân viên</button>
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
