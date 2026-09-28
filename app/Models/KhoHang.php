<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KhoHang extends Model
{
    protected $table = 'kho_hang';

    const UPDATED_AT = 'ngay_cap_nhat';
    public $timestamps = false;

    protected $fillable = [
        'id_sach',
        'so_luong_ton',
        'so_luong_dat_truoc',
    ];

    public function sach()
    {
        return $this->belongsTo(Sach::class, 'id_sach');
    }
}
