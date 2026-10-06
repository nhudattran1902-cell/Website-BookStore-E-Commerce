<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChiTietPhieuNhap extends Model
{
    protected $table = 'chi_tiet_phieu_nhap';

    public $timestamps = false;

    protected $fillable = [
        'id_phieu_nhap',
        'id_sach',
        'so_luong',
        'don_gia_nhap',
        'thanh_tien',
    ];

    public function phieuNhap(): BelongsTo
    {
        return $this->belongsTo(PhieuNhapKho::class, 'id_phieu_nhap');
    }

    public function sach(): BelongsTo
    {
        return $this->belongsTo(Sach::class, 'id_sach');
    }
}
