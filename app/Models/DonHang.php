<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DonHang extends Model
{
    protected $table = 'don_hang';

    const CREATED_AT = 'ngay_tao';
    const UPDATED_AT = 'ngay_cap_nhat';

    protected $fillable = [
        'ma_don_hang',
        'id_nguoi_dung',
        'tong_tien',
        'so_tien_giam_gia',
        'thanh_tien',
        'trang_thai',
        'dia_chi_giao_hang',
        'ghi_chu',
    ];

    // Quan hệ: Đơn hàng thuộc 1 Người dùng
    public function nguoiDung()
    {
        return $this->belongsTo(NguoiDung::class, 'id_nguoi_dung');
    }

    // Quan hệ: Đơn hàng có nhiều Chi tiết đơn hàng
    public function chiTietDonHang()
    {
        return $this->hasMany(ChiTietDonHang::class, 'id_don_hang');
    }

    // Quan hệ: Đơn hàng có 1 Thông tin thanh toán
    public function thanhToan()
    {
        return $this->hasOne(ThanhToan::class, 'id_don_hang');
    }
}
