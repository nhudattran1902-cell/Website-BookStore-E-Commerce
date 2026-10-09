<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NhaXuatBan extends Model
{
    protected $table = 'nha_xuat_ban';

    const CREATED_AT = 'ngay_tao';

    const UPDATED_AT = 'ngay_cap_nhat';

    protected $fillable = [
        'ten_nxb',
        'dia_chi',
        'website',
        'email',
        'logo',
    ];

    // Quan hệ 1 nhà xuất bản có nhiều sách
    public function saches(): HasMany
    {
        return $this->hasMany(Sach::class, 'id_nha_xuat_ban', 'id');
    }
}
