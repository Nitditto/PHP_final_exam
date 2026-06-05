<?php
$nav_items = [
  [
    'module' => 'dashboard',
    'href' => $dashboard_href,
    'label' => 'Dashboard',
  ],
  [
    'module' => 'nhan_vien',
    'href' => $nav_prefix . 'nhan_vien/index.php',
    'label' => 'Nhân viên',
  ],
  [
    'module' => 'ca_lam',
    'href' => $nav_prefix . 'ca_lam/index.php',
    'label' => 'Ca làm việc',
  ],
  [
    'module' => 'du_an',
    'href' => $nav_prefix . 'du_an/index.php',
    'label' => 'Dự án',
  ],
  [
    'module' => 'hop_dong',
    'href' => $nav_prefix . 'hop_dong/index.php',
    'label' => 'Hợp đồng',
  ],
  [
    'module' => 'khen_thuong',
    'href' => $nav_prefix . 'khen_thuong/index.php',
    'label' => 'Khen thưởng',
  ],
];
?>

<aside class="sidebar">
  <nav class="sidebar-nav" aria-label="Main navigation">
    <?php foreach ($nav_items as $item): ?>
      <a href="<?= $item['href'] ?>" class="<?= $current_module === $item['module'] ? 'active' : '' ?>">
        <?= $item['label'] ?>
      </a>
    <?php endforeach; ?>
  </nav>
</aside>
