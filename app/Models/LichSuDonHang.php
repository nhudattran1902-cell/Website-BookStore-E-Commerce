<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LichSuDonHang extends Model
{
    protected $table = 'lich_su_don_hang';

    public $timestamps = false; // Bảng chỉ có 1 cột ngay_tao

    protected $fillable = [
        'id_don_hang',
        'trang_thai_cu',
        'trang_thai_moi',
        'id_nguoi_thay_doi',
        'ghi_chu',
        'ngay_tao',
        'vi_tri',
        'nguon',
        'ma_su_kien',
    ];

    public function donHang(): BelongsTo
    {
        return $this->belongsTo(DonHang::class, 'id_don_hang');
    }

    public function nguoiThayDoi(): BelongsTo
    {
        return $this->belongsTo(NguoiDung::class, 'id_nguoi_thay_doi');
    }
}
