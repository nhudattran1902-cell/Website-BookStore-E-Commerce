<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GioHang extends Model
{
    protected $table = 'gio_hang';

    const CREATED_AT = 'ngay_tao';

    const UPDATED_AT = 'ngay_cap_nhat';

    protected $fillable = [
        'id_nguoi_dung',
    ];

    public function chiTietGioHang(): HasMany
    {
        return $this->hasMany(ChiTietGioHang::class, 'id_gio_hang');
    }

    public function nguoiDung(): BelongsTo
    {
        return $this->belongsTo(NguoiDung::class, 'id_nguoi_dung');
    }
}
