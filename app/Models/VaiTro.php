<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VaiTro extends Model
{
    protected $table = 'vai_tro';

    const CREATED_AT = 'ngay_tao';
    const UPDATED_AT = 'ngay_cap_nhat';

    protected $fillable = [
        'ten_vai_tro',
        'mo_ta',
    ];

    public function nguoiDung()
    {
        return $this->belongsToMany(
            NguoiDung::class,
            'vai_tro_nguoi_dung',
            'id_vai_tro',
            'id_nguoi_dung'
        );
    }
}
