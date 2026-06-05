<?php
require_once '../includes/db_connect.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id > 0) {
    // Với cấu trúc ON DELETE CASCADE và ON DELETE SET NULL trong DB,
    // Việc xóa ở bảng cha Nhan_vien sẽ hoàn toàn tự động xử lý các ràng buộc liên quan.
    $sql = "DELETE FROM Nhan_vien WHERE id = ?";
    if ($stmt = $mysqli->prepare($sql)) {
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
    }
}
header("Location: index.php");
exit();
?>