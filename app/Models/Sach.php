<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sach extends Model
{
    protected $table = 'sach';

    // 1. Chỉ định lại tên 2 cột Timestamps trong CSDL nha_sach_db
    const CREATED_AT = 'ngay_tao';
    const UPDATED_AT = 'ngay_cap_nhat';

    protected $fillable = [
        'tieu_de',
        'duong_dan_tinh',
        'id_the_loai',
        'id_nha_xuat_ban',
        'id_tac_gia',
        'gia_ban',
        'gia_khuyen_mai',
        'anh_bia',
        'mo_ta',
        'nam_xuat_ban',
        'noi_bat',
        'ban_chay',
    ];

    // Quan hệ: Sách thuộc 1 Thể loại
    public function theLoai()
    {
        return $this->belongsTo(TheLoai::class, 'id_the_loai');
    }

    // Quan hệ: Sách thuộc 1 Tác giả
    public function tacGia()
    {
        return $this->belongsTo(TacGia::class, 'id_tac_gia');
    }

    // Quan hệ: Sách thuộc 1 Nhà xuất bản
    public function nhaXuatBan()
    {
        return $this->belongsTo(NhaXuatBan::class, 'id_nha_xuat_ban');
    }

    // Quan hệ: Sách có nhiều Trang sách (đọc thử)
    public function trangSach()
    {
        return $this->hasMany(TrangSach::class, 'id_sach');
    }

    // Quan hệ: Sách có 1 Kho hàng
    public function khoHang()
    {
        return $this->hasOne(KhoHang::class, 'id_sach');
    }

    // Quan hệ: Các hình ảnh phụ của sách
    public function hinhAnh()
    {
        return $this->hasMany(HinhAnhSach::class, 'id_sach');
    }
}
