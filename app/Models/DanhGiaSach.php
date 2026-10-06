<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DanhGiaSach extends Model
{
    protected $table = 'danh_gia';

    const CREATED_AT = 'ngay_tao';

    const UPDATED_AT = 'ngay_cap_nhat';

    protected $fillable = [
        'id_nguoi_dung',
        'id_sach',
        'so_sao',
        'binh_luan',
        'da_duyet',
    ];

    protected $casts = [
        'so_sao' => 'integer',
        'da_duyet' => 'boolean',
    ];

    public function nguoiDung(): BelongsTo
    {
        return $this->belongsTo(NguoiDung::class, 'id_nguoi_dung');
    }

    public function sach(): BelongsTo
    {
        return $this->belongsTo(Sach::class, 'id_sach');
    }

    public function binhLuans(): HasMany
    {
        return $this->hasMany(DanhGiaBinhLuan::class, 'danh_gia_id');
    }

    public function luotThich(): HasMany
    {
        return $this->hasMany(DanhGiaLuotThich::class, 'danh_gia_id');
    }
}
