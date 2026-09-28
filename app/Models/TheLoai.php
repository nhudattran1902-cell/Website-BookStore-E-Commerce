<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TheLoai extends Model
{
    protected $table = 'the_loai';

    const CREATED_AT = 'ngay_tao';
    const UPDATED_AT = 'ngay_cap_nhat';

    protected $fillable = [
        'ten_the_loai',
        'duong_dan_tinh',
        'mo_ta',
    ];

    // Quan hệ: 1 Thể loại có nhiều Sách
    public function sach()
    {
        return $this->hasMany(Sach::class, 'id_the_loai');
    }
}
