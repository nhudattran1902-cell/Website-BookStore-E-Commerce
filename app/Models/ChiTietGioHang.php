<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChiTietGioHang extends Model
{
    protected $table = 'chi_tiet_gio_hang';

    const CREATED_AT = 'ngay_tao';
    const UPDATED_AT = 'ngay_cap_nhat';

    protected $fillable = [
        'id_gio_hang',
        'id_sach',
        'so_luong',
    ];

    public function gioHang()
    {
        return $this->belongsTo(GioHang::class, 'id_gio_hang');
    }

    public function sach()
    {
        return $this->belongsTo(Sach::class, 'id_sach');
    }
}
