<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class DonHang extends Model
{
    protected $table = 'don_hang';

    // Đổi tên 2 cột timestamps phù hợp với CSDL nha_sach_db
    const CREATED_AT = 'ngay_tao';

    const UPDATED_AT = 'ngay_cap_nhat';

    protected $fillable = [
        'ma_don_hang',
        'id_nguoi_dung',
        'id_ma_giam_gia',
        'ten_nguoi_nhan',
        'sdt_nguoi_nhan',
        'dia_chi_nhan',
        'dia_chi_giao_hang',
        'tong_tien',
        'so_tien_giam_gia',
        'thanh_tien',
        'trang_thai',
        'ghi_chu',
        'ma_van_don',
        'don_vi_van_chuyen',
        'tracking_url',
        'da_giu_ton',
        'thanh_toan_het_han_at',
    ];

    protected function casts(): array
    {
        return [
            'da_giu_ton' => 'boolean',
            'thanh_toan_het_han_at' => 'datetime',
        ];
    }

    // Quan hệ: Đơn hàng thuộc 1 Người dùng
    public function nguoiDung(): BelongsTo
    {
        return $this->belongsTo(NguoiDung::class, 'id_nguoi_dung')->withTrashed();
    }

    public function maGiamGia(): BelongsTo
    {
        return $this->belongsTo(MaGiamGia::class, 'id_ma_giam_gia');
    }

    // Quan hệ: Đơn hàng có nhiều Chi tiết đơn hàng
    public function chiTietDonHang(): HasMany
    {
        return $this->hasMany(ChiTietDonHang::class, 'id_don_hang');
    }

    // Quan hệ: Đơn hàng có 1 Thông tin thanh toán
    public function thanhToan(): HasOne
    {
        return $this->hasOne(ThanhToan::class, 'id_don_hang');
    }

    // Quan hệ: Đơn hàng có nhiều Lịch sử thay đổi trạng thái
    public function lichSuDonHang(): HasMany
    {
        return $this->hasMany(LichSuDonHang::class, 'id_don_hang')->orderByDesc('ngay_tao');
    }
}
