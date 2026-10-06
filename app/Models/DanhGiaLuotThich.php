<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DanhGiaLuotThich extends Model
{
    protected $table = 'danh_gia_luot_thich';

    const CREATED_AT = 'ngay_tao';

    public $timestamps = false;

    protected $fillable = [
        'danh_gia_id',
        'user_id',
    ];

    public function danhGia(): BelongsTo
    {
        return $this->belongsTo(DanhGiaSach::class, 'danh_gia_id');
    }

    public function nguoiDung(): BelongsTo
    {
        return $this->belongsTo(NguoiDung::class, 'user_id');
    }
}
