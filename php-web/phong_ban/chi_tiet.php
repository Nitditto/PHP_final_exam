<?php
require_once '../includes/db_connect.php';

function section_avatar_url($section_name)
{
  return 'https://ui-avatars.com/api/?name=' . urlencode($section_name) . '&background=0f172a&color=fff&size=128&bold=true';
}

function section_manager_summary($members)
{
  $managers = array_values(array_filter($members, static function ($member) {
    return (int) $member['truong_ban'] === 1;
  }));

  if (empty($managers)) {
    return 'Chưa chỉ định';
  }

  $manager_names = array_map(static function ($manager) {
    return $manager['ten'];
  }, $managers);

  if (count($manager_names) === 1) {
    return $manager_names[0];
  }

  return $manager_names[0] . ' +' . (count($manager_names) - 1);
}

function project_is_active($project)
{
  return (int) $project['is_active'] === 1;
}

$section_name = trim($_GET['name'] ?? '');

if ($section_name === '') {
  include '../includes/header.php';
  echo "<div class='card'>Không tìm thấy phòng ban.</div>";
  include '../includes/footer.php';
  exit();
}

$stmt = $mysqli->prepare(
  "SELECT *
   FROM Nhan_vien
   WHERE phong_ban = ?
   ORDER BY truong_ban DESC, ten ASC"
);
$stmt->bind_param("s", $section_name);
$stmt->execute();
$members = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$stmt = $mysqli->prepare(
  "SELECT Du_an.*, Nhan_vien.ten AS manager_name,
          CASE
            WHEN Du_an.ngay_ket_thuc IS NULL OR Du_an.ngay_ket_thuc >= CURDATE() THEN 1
            ELSE 0
          END AS is_active
   FROM Du_an
   LEFT JOIN Nhan_vien ON Du_an.id_nhan_vien = Nhan_vien.id
   WHERE Du_an.ten_phong_ban = ?
   ORDER BY
     CASE
       WHEN Du_an.ngay_ket_thuc IS NULL OR Du_an.ngay_ket_thuc >= CURDATE() THEN 0
       ELSE 1
     END ASC,
     CASE
       WHEN Du_an.ngay_ket_thuc IS NULL OR Du_an.ngay_ket_thuc >= CURDATE() THEN COALESCE(Du_an.ngay_ket_thuc, '9999-12-31')
       ELSE Du_an.ngay_ket_thuc
     END ASC,
     Du_an.ten_du_an ASC"
);
$stmt->bind_param("s", $section_name);
$stmt->execute();
$projects = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

if (empty($members) && empty($projects)) {
  include '../includes/header.php';
  echo "<div class='card'>Không tìm thấy phòng ban.</div>";
  include '../includes/footer.php';
  exit();
}

$member_count = count($members);
$manager_count = count(array_filter($members, static function ($member) {
  return (int) $member['truong_ban'] === 1;
}));
$active_projects = count(array_filter($projects, 'project_is_active'));
$total_projects = count($projects);
$average_salary = $member_count > 0
  ? array_sum(array_map(static function ($member) {
      return (float) $member['luong'];
    }, $members)) / $member_count
  : 0;

$avatar_url = section_avatar_url($section_name);
$manager_summary = section_manager_summary($members);

include '../includes/header.php';
?>

<div class="page-heading">
  <div>
    <h1>Chi tiết phòng ban</h1>
    <p>Tổng quan thành viên và các dự án đang được phòng ban quản lý.</p>
  </div>
  <a href="../nhan_vien/index.php?phong_ban=<?= urlencode($section_name) ?>" class="btn btn-secondary">Xem danh sách nhân viên</a>
</div>

<div class="profile-layout">
  <section class="card profile-card">
    <div class="profile-header">
      <img src="<?= htmlspecialchars($avatar_url) ?>" alt="<?= htmlspecialchars($section_name) ?>" class="profile-avatar">
      <div class="profile-title">
        <h2><?= htmlspecialchars($section_name) ?></h2>
        <p>Phòng ban</p>
        <?php if ($manager_count > 0): ?>
          <span class="status-badge leader"><?= $manager_count > 1 ? $manager_count . ' trưởng ban' : 'Có trưởng ban' ?></span>
        <?php endif; ?>
      </div>
    </div>

    <dl class="info-list">
      <div>
        <dt>Trưởng ban</dt>
        <dd><?= htmlspecialchars($manager_summary) ?></dd>
      </div>
      <div>
        <dt>Số thành viên</dt>
        <dd><?= number_format($member_count) ?></dd>
      </div>
      <div>
        <dt>Dự án active</dt>
        <dd><?= number_format($active_projects) ?></dd>
      </div>
      <div>
        <dt>Tổng dự án</dt>
        <dd><?= number_format($total_projects) ?></dd>
      </div>
      <div>
        <dt>Lương trung bình</dt>
        <dd><?= $member_count > 0 ? number_format($average_salary) . ' đ' : 'Chưa có' ?></dd>
      </div>
      <div>
        <dt>Nhân sự quản lý</dt>
        <dd><?= number_format($manager_count) ?></dd>
      </div>
    </dl>
  </section>

  <section class="card">
    <div class="card-header">
      <h2>Thành viên phòng ban</h2>
    </div>
    <div class="list-stack">
      <?php foreach ($members as $member): ?>
        <a href="../nhan_vien/chi_tiet.php?id=<?= $member['id'] ?>" class="detail-item detail-item-link">
          <div>
            <div class="text-strong"><?= htmlspecialchars($member['ten']) ?></div>
            <div class="sub-text">
              <?= htmlspecialchars($member['chuc_vu'] ?: 'Chưa có chức vụ') ?>
              <?php if (!empty($member['email'])): ?>
                • <?= htmlspecialchars($member['email']) ?>
              <?php endif; ?>
            </div>
          </div>
          <?php if ($member['truong_ban']): ?>
            <span class="badge badge-success">Trưởng ban</span>
          <?php else: ?>
            <span class="badge badge-info">Thành viên</span>
          <?php endif; ?>
        </a>
      <?php endforeach; ?>
      <?php if (empty($members)): ?>
        <div class="empty-inline">Phòng ban này chưa có thành viên.</div>
      <?php endif; ?>
    </div>
  </section>
</div>

<section class="card">
  <div class="card-header">
    <h2>Dự án được quản lý</h2>
  </div>
  <div class="table-container">
    <table>
      <thead>
        <tr>
          <th>Dự án</th>
          <th>Người phụ trách</th>
          <th>Thời hạn</th>
          <th>Trạng thái</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($projects as $project): ?>
          <tr>
            <td>
              <div class="text-strong"><?= htmlspecialchars($project['ten_du_an']) ?></div>
              <div class="sub-text"><?= htmlspecialchars($project['mo_ta'] ?: 'Chưa có mô tả') ?></div>
            </td>
            <td><?= htmlspecialchars($project['manager_name'] ?: 'Chưa gán') ?></td>
            <td>
              <div class="text-small"><?= htmlspecialchars($project['ngay_bat_dau'] ?: 'Chưa có') ?></div>
              <div class="text-small-muted">đến <?= htmlspecialchars($project['ngay_ket_thuc'] ?: 'Không thời hạn') ?></div>
            </td>
            <td>
              <span class="badge <?= project_is_active($project) ? 'badge-success' : 'badge-warning' ?>">
                <?= project_is_active($project) ? 'Active' : 'Inactive / completed' ?>
              </span>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (empty($projects)): ?>
          <tr><td colspan="4" class="empty-state">Phòng ban này chưa có dự án nào.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</section>

<?php include '../includes/footer.php'; ?>
