<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class TacGia extends Model
{
    protected $table = 'tac_gia';

    const CREATED_AT = 'ngay_tao';
    const UPDATED_AT = 'ngay_cap_nhat';

    protected $fillable = [
        'ten_tac_gia',
        'tieu_su',
        'anh_dai_dien',
    ];

    // Quan hệ Nhiều - Nhiều với Sach qua bảng trung gian sach_tac_gia
    public function sach(): BelongsToMany
    {
        return $this->belongsToMany(
            Sach::class,
            'sach_tac_gia', // Bảng trung gian
            'id_tac_gia',   // Khóa ngoại TacGia trong bảng trung gian
            'id_sach'       // Khóa ngoại Sach trong bảng trung gian
        );
    }
}
