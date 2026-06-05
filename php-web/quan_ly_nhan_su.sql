-- Tạo database quan_ly_nhan_su
CREATE DATABASE IF NOT EXISTS quan_ly_nhan_su;
USE quan_ly_nhan_su;
-- ==========================================
-- 1. TẠO BẢNG GỐC: Nhan_vien
-- Phải tạo bảng này đầu tiên vì các bảng khác lấy ID từ đây
-- ==========================================
CREATE TABLE IF NOT EXISTS Nhan_vien (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ten VARCHAR(255) NOT NULL,
    sdt VARCHAR(15),
    email VARCHAR(255),
    dia_chi TEXT,
    ngay_sinh DATE,
    gioi_tinh VARCHAR(10),
    phong_ban VARCHAR(100),
    chuc_vu VARCHAR(100),
    truong_ban BOOLEAN DEFAULT FALSE,
    luong DECIMAL(15, 2)
);
-- ==========================================
-- 2. TẠO BẢNG: Ca_lam
-- ==========================================
CREATE TABLE IF NOT EXISTS Ca_lam (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_nhan_vien INT NOT NULL,
    gio_bat_dau TIME,
    gio_ket_thuc TIME,
    ngay_lam DATE,
    he_so DECIMAL(3, 2) DEFAULT 1.00,
    tang_ca BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (id_nhan_vien) REFERENCES Nhan_vien(id) ON DELETE CASCADE
);
-- ==========================================
-- 3. TẠO BẢNG: Du_an
-- (Không dùng bảng trung gian, 1 dự án do 1 nhân viên phụ trách chính)
-- ==========================================
CREATE TABLE IF NOT EXISTS Du_an (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ten_du_an VARCHAR(255) NOT NULL,
    mo_ta TEXT,
    ngay_bat_dau DATE,
    ngay_ket_thuc DATE,
    ten_phong_ban VARCHAR(100),
    id_nhan_vien INT,
    -- Nếu nhân viên nghỉ việc, dự án vẫn còn nhưng người phụ trách sẽ bị trống (NULL)
    FOREIGN KEY (id_nhan_vien) REFERENCES Nhan_vien(id) ON DELETE
    SET NULL
);
-- ==========================================
-- 4. TẠO BẢNG: Hop_dong_lao_dong
-- ==========================================
CREATE TABLE IF NOT EXISTS Hop_dong_lao_dong (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_nhan_vien INT NOT NULL,
    loai_hop_dong VARCHAR(100),
    ngay_bat_dau DATE NOT NULL,
    ngay_ket_thuc DATE,
    luong_co_ban DECIMAL(15, 2),
    trang_thai VARCHAR(50) DEFAULT 'Đang hiệu lực',
    FOREIGN KEY (id_nhan_vien) REFERENCES Nhan_vien(id) ON DELETE CASCADE
);
-- ==========================================
-- 5. TẠO BẢNG: Danh_gia_khen_thuong
-- ==========================================
CREATE TABLE IF NOT EXISTS Danh_gia_khen_thuong (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_nhan_vien INT NOT NULL,
    loai_khen_thuong VARCHAR(100) NOT NULL,
    ngay_ghi_nhan DATE NOT NULL,
    so_tien DECIMAL(15, 2) DEFAULT 0,
    ly_do TEXT,
    FOREIGN KEY (id_nhan_vien) REFERENCES Nhan_vien(id) ON DELETE CASCADE
);