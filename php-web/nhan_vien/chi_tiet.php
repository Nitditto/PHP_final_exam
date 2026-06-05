<?php
require_once '../includes/db_connect.php';

function section_detail_link($department_name)
{
  $department_name = trim((string) $department_name);
  if ($department_name === '') {
    return null;
  }

  return '../phong_ban/chi_tiet.php?name=' . urlencode($department_name);
}

function award_title($award)
{
  $reason = trim((string) ($award['ly_do'] ?? ''));
  if ($reason !== '') {
    return $reason;
  }

  return (string) ($award['loai_khen_thuong'] ?? 'Không có nội dung');
}

function award_subtext($award)
{
  $reason = trim((string) ($award['ly_do'] ?? ''));
  if ($reason !== '') {
    return (string) ($award['loai_khen_thuong'] ?? '');
  }

  return 'Không có lý do bổ sung';
}

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

$stmt = $mysqli->prepare("SELECT * FROM Nhan_vien WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$employee = $stmt->get_result()->fetch_assoc();
// là mảng chứa thông tin nhân viên có id tương ứng
$stmt->close();

if (!$employee) {
  include '../includes/header.php';
  echo "<div class='card'>Không tìm thấy nhân viên.</div>";
  include '../includes/footer.php';
  exit();
}

$stmt = $mysqli->prepare(
  "SELECT * FROM Du_an
   WHERE id_nhan_vien = ?
     AND (ngay_ket_thuc IS NULL OR ngay_ket_thuc >= CURDATE())
   ORDER BY ngay_bat_dau DESC, id DESC"
);
$stmt->bind_param("i", $id);
$stmt->execute();
$projects = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$stmt = $mysqli->prepare(
  "SELECT * FROM Hop_dong_lao_dong
   WHERE id_nhan_vien = ? AND trang_thai = 'Đang hiệu lực'
   ORDER BY ngay_bat_dau DESC, id DESC"
);
$stmt->bind_param("i", $id);
$stmt->execute();
$contracts = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$stmt = $mysqli->prepare(
  "SELECT * FROM Danh_gia_khen_thuong
   WHERE id_nhan_vien = ?
   ORDER BY ngay_ghi_nhan DESC, id DESC
   LIMIT 5"
);
$stmt->bind_param("i", $id);
$stmt->execute();
$awards = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$stmt = $mysqli->prepare(
  "SELECT * FROM Ca_lam
   WHERE id_nhan_vien = ?
   ORDER BY ngay_lam DESC, gio_bat_dau DESC
   LIMIT 5"
);
$stmt->bind_param("i", $id);
$stmt->execute();
$shifts = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$avatar_url = 'https://ui-avatars.com/api/?name=' . urlencode($employee['ten']) . '&background=111827&color=fff&size=128&bold=true';
$employee_department_link = section_detail_link($employee['phong_ban']);

include '../includes/header.php';
?>

<div class="page-heading">
  <div>
    <h1>Chi tiết nhân viên</h1>
    <p>Hồ sơ, dự án, hợp đồng và hoạt động gần đây.</p>
  </div>
  <a href="index.php" class="btn btn-secondary">Quay lại</a>
</div>

<div class="profile-layout">
  <section class="card profile-card">
    <div class="profile-header">
      <img src="<?= htmlspecialchars($avatar_url) ?>" alt="<?= htmlspecialchars($employee['ten']) ?>" class="profile-avatar">
      <div class="profile-title">
        <h2><?= htmlspecialchars($employee['ten']) ?></h2>
        <p><?= htmlspecialchars($employee['chuc_vu'] ?: 'Chưa có chức vụ') ?></p>
        <?php if ($employee['truong_ban']): ?>
          <span class="status-badge leader">Trưởng ban</span>
        <?php endif; ?>
      </div>
    </div>

    <div class="profile-actions">
      <a href="sua.php?id=<?= $employee['id'] ?>" class="btn btn-primary">Sửa</a>
      <a href="xoa.php?id=<?= $employee['id'] ?>" class="btn btn-danger" onclick="return confirm('Bạn chắc chắn muốn xóa nhân viên này?');">Xóa</a>
    </div>

    <dl class="info-list">
      <div>
        <dt>Email</dt>
        <dd><?= htmlspecialchars($employee['email'] ?: 'Chưa có') ?></dd>
      </div>
      <div>
        <dt>Số điện thoại</dt>
        <dd><?= htmlspecialchars($employee['sdt'] ?: 'Chưa có') ?></dd>
      </div>
      <div>
        <dt>Phòng ban</dt>
        <dd>
          <?php if ($employee_department_link): ?>
            <a href="<?= htmlspecialchars($employee_department_link) ?>" class="inline-link">
              <?= htmlspecialchars($employee['phong_ban']) ?>
            </a>
          <?php else: ?>
            Chưa có
          <?php endif; ?>
        </dd>
      </div>
      <div>
        <dt>Ngày sinh</dt>
        <dd><?= htmlspecialchars($employee['ngay_sinh'] ?: 'Chưa có') ?></dd>
      </div>
      <div>
        <dt>Giới tính</dt>
        <dd><?= htmlspecialchars($employee['gioi_tinh'] ?: 'Chưa có') ?></dd>
      </div>
      <div>
        <dt>Lương</dt>
        <dd><?= number_format((float) $employee['luong']) ?> đ</dd>
      </div>
      <div class="info-span">
        <dt>Địa chỉ</dt>
        <dd><?= htmlspecialchars($employee['dia_chi'] ?: 'Chưa có') ?></dd>
      </div>
    </dl>
  </section>

  <section class="card">
    <div class="card-header">
      <h2>Dự án đang tham gia</h2>
    </div>
    <div class="list-stack">
      <?php foreach ($projects as $project): ?>
        <?php $department_link = section_detail_link($project['ten_phong_ban']); ?>
        <div class="detail-item">
          <div>
            <div class="text-strong"><?= htmlspecialchars($project['ten_du_an']) ?></div>
            <?php if ($department_link): ?>
              <a href="<?= htmlspecialchars($department_link) ?>" class="sub-text inline-link">
                <?= htmlspecialchars($project['ten_phong_ban']) ?>
              </a>
            <?php else: ?>
              <div class="sub-text">Chưa có phòng ban</div>
            <?php endif; ?>
          </div>
          <span class="badge badge-success">Phụ trách</span>
        </div>
      <?php endforeach; ?>
      <?php if (empty($projects)): ?>
        <div class="empty-inline">Chưa có dự án đang tham gia.</div>
      <?php endif; ?>
    </div>
  </section>
</div>

<div class="detail-grid">
  <section class="card">
    <div class="card-header">
      <h2>Hợp đồng hiệu lực</h2>
    </div>
    <div class="table-container">
      <table>
        <thead>
          <tr>
            <th>Loại HĐ</th>
            <th>Thời hạn</th>
            <th>Lương</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($contracts as $contract): ?>
            <tr>
              <td><?= htmlspecialchars($contract['loai_hop_dong']) ?></td>
              <td>
                <div class="text-small"><?= htmlspecialchars($contract['ngay_bat_dau']) ?></div>
                <div class="text-small-muted">đến <?= htmlspecialchars($contract['ngay_ket_thuc'] ?: 'Không thời hạn') ?></div>
              </td>
              <td class="text-primary-strong"><?= number_format((float) $contract['luong_co_ban']) ?> đ</td>
            </tr>
          <?php endforeach; ?>
          <?php if (empty($contracts)): ?>
            <tr><td colspan="3" class="empty-state">Chưa có hợp đồng hiệu lực.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </section>

  <section class="card">
    <div class="card-header">
      <h2>Khen thưởng gần đây</h2>
    </div>
    <div class="table-container">
      <table>
        <thead>
          <tr>
            <th>Nội dung</th>
            <th>Ngày</th>
            <th>Số tiền</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($awards as $award): ?>
            <tr>
              <td>
                <div class="text-strong"><?= htmlspecialchars(award_title($award)) ?></div>
                <div class="sub-text"><?= htmlspecialchars(award_subtext($award)) ?></div>
              </td>
              <td><?= htmlspecialchars($award['ngay_ghi_nhan']) ?></td>
              <td class="text-primary-strong"><?= number_format((float) $award['so_tien']) ?> đ</td>
            </tr>
          <?php endforeach; ?>
          <?php if (empty($awards)): ?>
            <tr><td colspan="3" class="empty-state">Chưa có ghi nhận gần đây.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </section>

  <section class="card detail-grid-wide">
    <div class="card-header">
      <h2>Ca làm gần đây</h2>
    </div>
    <div class="table-container">
      <table>
        <thead>
          <tr>
            <th>Ngày làm</th>
            <th>Giờ</th>
            <th>Hệ số</th>
            <th>Loại ca</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($shifts as $shift): ?>
            <tr>
              <td><?= htmlspecialchars($shift['ngay_lam']) ?></td>
              <td><?= htmlspecialchars($shift['gio_bat_dau']) ?> - <?= htmlspecialchars($shift['gio_ket_thuc']) ?></td>
              <td><?= htmlspecialchars((string) $shift['he_so']) ?></td>
              <td>
                <span class="badge <?= $shift['tang_ca'] ? 'badge-warning' : 'badge-info' ?>">
                  <?= $shift['tang_ca'] ? 'Tăng ca' : 'Thường' ?>
                </span>
              </td>
            </tr>
          <?php endforeach; ?>
          <?php if (empty($shifts)): ?>
            <tr><td colspan="4" class="empty-state">Chưa có ca làm gần đây.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </section>
</div>

<?php include '../includes/footer.php'; ?>
