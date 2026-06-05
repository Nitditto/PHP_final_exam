# php-web

## Nhan_vien
- id: number
- ten: string
- sdt: number
- email: string
- dia_chi: string
- ngay_sinh: date
- gioi_tinh: string
- phong_ban: string
- chuc_vu: string
- truong_ban: boolean
- luong: number
primary key (id)

## Ca_lam
- id: number
- id_nhan_vien: number
- gio_bat_dau: time
- gio_ket_thuc: time
- ngay_lam: date
- he_so: number
- tang_ca: boolean
foreign key (id_nhan_vien) references Nhan_vien(id)
primary key (id)

## Du an
- id: number
- ten_du_an: string
- mo_ta: string
- ngay_bat_dau: date
- ngay_ket_thuc: date
- ten_phong_ban: string
- id_nhan_vien: number
foreign key (id_nhan_vien) references Nhan_vien(id)
primary key (id)

## Hop dong lao dong
- id: number
- id_nhan_vien: number
- loai_hop_dong: string
- ngay_bat_dau: date
- ngay_ket_thuc: date
- luong_co_ban: number
- trang_thai: string
foreign key (id_nhan_vien) references Nhan_vien(id)
primary key (id)

## Danh gia khen thuong
- id: number
- id_nhan_vien: number
- loai_khen_thuong: string
- ngay_ghi_nhan: date
- so_tien: number
- ly_do: string
foreign key (id_nhan_vien) references Nhan_vien(id)
primary key (id)
