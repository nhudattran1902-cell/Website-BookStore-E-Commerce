<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MaGiamGia extends Model
{
    protected $table = 'ma_giam_gia';

    const CREATED_AT = 'ngay_tao';

    const UPDATED_AT = 'ngay_cap_nhat';

    protected $fillable = [
        'ma_code',
        'loai_giam',
        'gia_tri',
        'gia_tri_toi_da',
        'don_toi_thieu',
        'gioi_han_luot',
        'da_su_dung',
        'ngay_bat_dau',
        'ngay_het_han',
        'dang_hoat_dong',
    ];

    protected function casts(): array
    {
        return [
            'gia_tri' => 'integer',
            'gia_tri_toi_da' => 'integer',
            'don_toi_thieu' => 'integer',
            'gioi_han_luot' => 'integer',
            'da_su_dung' => 'integer',
            'ngay_bat_dau' => 'datetime',
            'ngay_het_han' => 'datetime',
            'dang_hoat_dong' => 'boolean',
        ];
    }

    public function donHangs(): HasMany
    {
        return $this->hasMany(DonHang::class, 'id_ma_giam_gia');
    }
}
