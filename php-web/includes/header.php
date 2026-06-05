<?php
$current_path = $_SERVER['PHP_SELF'];
$module_names = ['nhan_vien', 'ca_lam', 'du_an', 'hop_dong', 'khen_thuong', 'phong_ban'];
$current_dir = basename(dirname($current_path));
$is_module_page = in_array($current_dir, $module_names, true);
$current_module = $is_module_page ? $current_dir : 'dashboard';
$base_path = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
if ($is_module_page) {
  $base_path = rtrim(dirname($base_path), '/\\');
}
$base_path = $base_path === '' ? '' : $base_path;
$asset_prefix = $base_path;
$nav_prefix = $base_path . '/';
$dashboard_href = $base_path . '/index.php';
$css_href = $asset_prefix . '/assets/css/style.css';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Hệ Thống Quản Lý Nhân Sự</title>
  <link rel="stylesheet" href="<?= $css_href ?>">
</head>
<body class="module-<?= htmlspecialchars($current_module) ?>">
  <input type="checkbox" id="sidebar-toggle" class="sidebar-toggle" aria-label="Toggle sidebar">

  <div class="app-shell">
    <header class="topbar">
      <div class="topbar-left">
        <label for="sidebar-toggle" class="sidebar-toggle-button" aria-label="Toggle sidebar">☰</label>
        <a href="<?= $dashboard_href ?>" class="brand-link">Quản lý nhân viên</a>
      </div>
      <div class="admin-greeting">Hello, Truong!</div>
    </header>

    <div class="app-body">
      <?php include __DIR__ . '/sidebar.php'; ?>

      <div class="app-frame">
        <main class="main-content">
