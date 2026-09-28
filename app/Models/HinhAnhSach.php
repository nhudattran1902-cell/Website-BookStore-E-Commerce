<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HinhAnhSach extends Model
{
    protected $table = 'hinh_anh_sach';

    const CREATED_AT = 'ngay_tao';
    const UPDATED_AT = 'ngay_cap_nhat';

    protected $fillable = [
        'id_sach',
        'duong_dan_anh',
        'chu_thich',
        'thu_tu',
    ];

    // Quan hệ: Hình ảnh thuộc về 1 Sách
    public function sach()
    {
        return $this->belongsTo(Sach::class, 'id_sach');
    }
}

