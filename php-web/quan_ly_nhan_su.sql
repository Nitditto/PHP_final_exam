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

-- ==========================================
-- 6. DU LIEU MAU
-- Cac cau INSERT duoc viet dang idempotent de chay lai khong bi nhan doi data.
-- ==========================================
INSERT INTO Nhan_vien (ten, sdt, email, dia_chi, ngay_sinh, gioi_tinh, phong_ban, chuc_vu, truong_ban, luong)
SELECT 'Nguyen Van An', '0901000001', 'an.nguyen@example.com', 'Quan 1, TP HCM', '1990-02-15', 'Nam', 'Kinh doanh', 'Truong phong', TRUE, 28000000.00
WHERE NOT EXISTS (SELECT 1 FROM Nhan_vien WHERE email = 'an.nguyen@example.com');

INSERT INTO Nhan_vien (ten, sdt, email, dia_chi, ngay_sinh, gioi_tinh, phong_ban, chuc_vu, truong_ban, luong)
SELECT 'Tran Thi Binh', '0901000002', 'binh.tran@example.com', 'Quan 3, TP HCM', '1994-07-20', 'Nu', 'Nhan su', 'Chuyen vien nhan su', FALSE, 16000000.00
WHERE NOT EXISTS (SELECT 1 FROM Nhan_vien WHERE email = 'binh.tran@example.com');

INSERT INTO Nhan_vien (ten, sdt, email, dia_chi, ngay_sinh, gioi_tinh, phong_ban, chuc_vu, truong_ban, luong)
SELECT 'Le Minh Chau', '0901000003', 'chau.le@example.com', 'Thu Duc, TP HCM', '1992-11-05', 'Nu', 'Ky thuat', 'Lap trinh vien', FALSE, 22000000.00
WHERE NOT EXISTS (SELECT 1 FROM Nhan_vien WHERE email = 'chau.le@example.com');

INSERT INTO Nhan_vien (ten, sdt, email, dia_chi, ngay_sinh, gioi_tinh, phong_ban, chuc_vu, truong_ban, luong)
SELECT 'Pham Quoc Dung', '0901000004', 'dung.pham@example.com', 'Quan 7, TP HCM', '1988-09-12', 'Nam', 'Ky thuat', 'Truong nhom', TRUE, 30000000.00
WHERE NOT EXISTS (SELECT 1 FROM Nhan_vien WHERE email = 'dung.pham@example.com');

INSERT INTO Nhan_vien (ten, sdt, email, dia_chi, ngay_sinh, gioi_tinh, phong_ban, chuc_vu, truong_ban, luong)
SELECT 'Hoang Gia Huy', '0901000005', 'huy.hoang@example.com', 'Binh Thanh, TP HCM', '1996-03-08', 'Nam', 'Marketing', 'Nhan vien marketing', FALSE, 15000000.00
WHERE NOT EXISTS (SELECT 1 FROM Nhan_vien WHERE email = 'huy.hoang@example.com');

INSERT INTO Nhan_vien (ten, sdt, email, dia_chi, ngay_sinh, gioi_tinh, phong_ban, chuc_vu, truong_ban, luong)
SELECT 'Do Thi Khanh Linh', '0901000006', 'linh.do@example.com', 'Quan 10, TP HCM', '1991-05-28', 'Nu', 'Ke toan', 'Ke toan truong', TRUE, 26000000.00
WHERE NOT EXISTS (SELECT 1 FROM Nhan_vien WHERE email = 'linh.do@example.com');

INSERT INTO Ca_lam (id_nhan_vien, gio_bat_dau, gio_ket_thuc, ngay_lam, he_so, tang_ca)
SELECT id, '08:00:00', '17:00:00', '2026-06-01', 1.00, FALSE FROM Nhan_vien
WHERE email = 'an.nguyen@example.com'
AND NOT EXISTS (SELECT 1 FROM Ca_lam WHERE id_nhan_vien = Nhan_vien.id AND ngay_lam = '2026-06-01' AND gio_bat_dau = '08:00:00');

INSERT INTO Ca_lam (id_nhan_vien, gio_bat_dau, gio_ket_thuc, ngay_lam, he_so, tang_ca)
SELECT id, '08:00:00', '17:00:00', '2026-06-01', 1.00, FALSE FROM Nhan_vien
WHERE email = 'binh.tran@example.com'
AND NOT EXISTS (SELECT 1 FROM Ca_lam WHERE id_nhan_vien = Nhan_vien.id AND ngay_lam = '2026-06-01' AND gio_bat_dau = '08:00:00');

INSERT INTO Ca_lam (id_nhan_vien, gio_bat_dau, gio_ket_thuc, ngay_lam, he_so, tang_ca)
SELECT id, '09:00:00', '18:00:00', '2026-06-02', 1.00, FALSE FROM Nhan_vien
WHERE email = 'chau.le@example.com'
AND NOT EXISTS (SELECT 1 FROM Ca_lam WHERE id_nhan_vien = Nhan_vien.id AND ngay_lam = '2026-06-02' AND gio_bat_dau = '09:00:00');

INSERT INTO Ca_lam (id_nhan_vien, gio_bat_dau, gio_ket_thuc, ngay_lam, he_so, tang_ca)
SELECT id, '18:00:00', '21:00:00', '2026-06-03', 1.50, TRUE FROM Nhan_vien
WHERE email = 'dung.pham@example.com'
AND NOT EXISTS (SELECT 1 FROM Ca_lam WHERE id_nhan_vien = Nhan_vien.id AND ngay_lam = '2026-06-03' AND gio_bat_dau = '18:00:00');

INSERT INTO Ca_lam (id_nhan_vien, gio_bat_dau, gio_ket_thuc, ngay_lam, he_so, tang_ca)
SELECT id, '08:30:00', '17:30:00', '2026-06-04', 1.00, FALSE FROM Nhan_vien
WHERE email = 'huy.hoang@example.com'
AND NOT EXISTS (SELECT 1 FROM Ca_lam WHERE id_nhan_vien = Nhan_vien.id AND ngay_lam = '2026-06-04' AND gio_bat_dau = '08:30:00');

INSERT INTO Du_an (ten_du_an, mo_ta, ngay_bat_dau, ngay_ket_thuc, ten_phong_ban, id_nhan_vien)
SELECT 'CRM Noi Bo', 'Xay dung he thong quan ly khach hang cho phong kinh doanh.', '2026-01-10', '2026-08-30', 'Kinh doanh', id FROM Nhan_vien
WHERE email = 'an.nguyen@example.com'
AND NOT EXISTS (SELECT 1 FROM Du_an WHERE ten_du_an = 'CRM Noi Bo');

INSERT INTO Du_an (ten_du_an, mo_ta, ngay_bat_dau, ngay_ket_thuc, ten_phong_ban, id_nhan_vien)
SELECT 'Cong Thong Tin Nhan Su', 'So hoa quy trinh nghi phep, hop dong va cham cong.', '2026-02-01', NULL, 'Nhan su', id FROM Nhan_vien
WHERE email = 'binh.tran@example.com'
AND NOT EXISTS (SELECT 1 FROM Du_an WHERE ten_du_an = 'Cong Thong Tin Nhan Su');

INSERT INTO Du_an (ten_du_an, mo_ta, ngay_bat_dau, ngay_ket_thuc, ten_phong_ban, id_nhan_vien)
SELECT 'Ung Dung Mobile Ban Hang', 'Phat trien ung dung dat hang cho nhan vien kinh doanh.', '2026-03-15', '2026-12-15', 'Ky thuat', id FROM Nhan_vien
WHERE email = 'dung.pham@example.com'
AND NOT EXISTS (SELECT 1 FROM Du_an WHERE ten_du_an = 'Ung Dung Mobile Ban Hang');

INSERT INTO Hop_dong_lao_dong (id_nhan_vien, loai_hop_dong, ngay_bat_dau, ngay_ket_thuc, luong_co_ban, trang_thai)
SELECT id, 'Khong thoi han', '2024-01-01', NULL, 28000000.00, 'Dang hieu luc' FROM Nhan_vien
WHERE email = 'an.nguyen@example.com'
AND NOT EXISTS (SELECT 1 FROM Hop_dong_lao_dong WHERE id_nhan_vien = Nhan_vien.id AND ngay_bat_dau = '2024-01-01');

INSERT INTO Hop_dong_lao_dong (id_nhan_vien, loai_hop_dong, ngay_bat_dau, ngay_ket_thuc, luong_co_ban, trang_thai)
SELECT id, 'Co thoi han 2 nam', '2025-03-01', '2027-02-28', 16000000.00, 'Dang hieu luc' FROM Nhan_vien
WHERE email = 'binh.tran@example.com'
AND NOT EXISTS (SELECT 1 FROM Hop_dong_lao_dong WHERE id_nhan_vien = Nhan_vien.id AND ngay_bat_dau = '2025-03-01');

INSERT INTO Hop_dong_lao_dong (id_nhan_vien, loai_hop_dong, ngay_bat_dau, ngay_ket_thuc, luong_co_ban, trang_thai)
SELECT id, 'Co thoi han 1 nam', '2026-01-01', '2026-12-31', 22000000.00, 'Dang hieu luc' FROM Nhan_vien
WHERE email = 'chau.le@example.com'
AND NOT EXISTS (SELECT 1 FROM Hop_dong_lao_dong WHERE id_nhan_vien = Nhan_vien.id AND ngay_bat_dau = '2026-01-01');

INSERT INTO Hop_dong_lao_dong (id_nhan_vien, loai_hop_dong, ngay_bat_dau, ngay_ket_thuc, luong_co_ban, trang_thai)
SELECT id, 'Khong thoi han', '2023-06-15', NULL, 30000000.00, 'Dang hieu luc' FROM Nhan_vien
WHERE email = 'dung.pham@example.com'
AND NOT EXISTS (SELECT 1 FROM Hop_dong_lao_dong WHERE id_nhan_vien = Nhan_vien.id AND ngay_bat_dau = '2023-06-15');

INSERT INTO Hop_dong_lao_dong (id_nhan_vien, loai_hop_dong, ngay_bat_dau, ngay_ket_thuc, luong_co_ban, trang_thai)
SELECT id, 'Thu viec', '2026-05-01', '2026-06-30', 12000000.00, 'Dang hieu luc' FROM Nhan_vien
WHERE email = 'huy.hoang@example.com'
AND NOT EXISTS (SELECT 1 FROM Hop_dong_lao_dong WHERE id_nhan_vien = Nhan_vien.id AND ngay_bat_dau = '2026-05-01');

INSERT INTO Danh_gia_khen_thuong (id_nhan_vien, loai_khen_thuong, ngay_ghi_nhan, so_tien, ly_do)
SELECT id, 'Khen thuong', '2026-04-30', 3000000.00, 'Dat doanh so quy 2 vuot muc tieu.' FROM Nhan_vien
WHERE email = 'an.nguyen@example.com'
AND NOT EXISTS (SELECT 1 FROM Danh_gia_khen_thuong WHERE id_nhan_vien = Nhan_vien.id AND ngay_ghi_nhan = '2026-04-30' AND loai_khen_thuong = 'Khen thuong');

INSERT INTO Danh_gia_khen_thuong (id_nhan_vien, loai_khen_thuong, ngay_ghi_nhan, so_tien, ly_do)
SELECT id, 'Khen thuong', '2026-05-15', 2000000.00, 'Hoan thanh module cham cong dung tien do.' FROM Nhan_vien
WHERE email = 'chau.le@example.com'
AND NOT EXISTS (SELECT 1 FROM Danh_gia_khen_thuong WHERE id_nhan_vien = Nhan_vien.id AND ngay_ghi_nhan = '2026-05-15' AND loai_khen_thuong = 'Khen thuong');

INSERT INTO Danh_gia_khen_thuong (id_nhan_vien, loai_khen_thuong, ngay_ghi_nhan, so_tien, ly_do)
SELECT id, 'Ky luat', '2026-05-20', 500000.00, 'Di tre qua so lan quy dinh trong thang.' FROM Nhan_vien
WHERE email = 'huy.hoang@example.com'
AND NOT EXISTS (SELECT 1 FROM Danh_gia_khen_thuong WHERE id_nhan_vien = Nhan_vien.id AND ngay_ghi_nhan = '2026-05-20' AND loai_khen_thuong = 'Ky luat');
