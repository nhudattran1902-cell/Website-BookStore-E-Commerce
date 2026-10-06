<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PhieuNhapKho extends Model
{
    protected $table = 'phieu_nhap_kho';

    const CREATED_AT = 'ngay_tao';

    const UPDATED_AT = 'ngay_cap_nhat';

    protected $fillable = [
        'ma_phieu',
        'id_nha_xuat_ban',
        'id_nguoi_nhap',
        'tong_tien',
        'ghi_chu',
    ];

    public function nhaXuatBan(): BelongsTo
    {
        return $this->belongsTo(NhaXuatBan::class, 'id_nha_xuat_ban');
    }

    public function nguoiNhap(): BelongsTo
    {
        return $this->belongsTo(NguoiDung::class, 'id_nguoi_nhap');
    }

    public function chiTietPhieuNhap(): HasMany
    {
        return $this->hasMany(ChiTietPhieuNhap::class, 'id_phieu_nhap');
    }
}
