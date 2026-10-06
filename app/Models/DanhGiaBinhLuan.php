<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DanhGiaBinhLuan extends Model
{
    protected $table = 'danh_gia_binh_luan';

    const CREATED_AT = 'ngay_tao';

    const UPDATED_AT = 'ngay_cap_nhat';

    protected $fillable = [
        'danh_gia_id',
        'user_id',
        'noi_dung',
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
