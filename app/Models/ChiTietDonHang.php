<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChiTietDonHang extends Model
{
    protected $table = 'chi_tiet_don_hang';
    public $timestamps = false;

    protected $fillable = [
        'id_don_hang',
        'id_sach',
        'don_gia',
        'so_luong',
        'thanh_tien',
    ];

    public function donHang()
    {
        return $this->belongsTo(DonHang::class, 'id_don_hang');
    }

    public function sach()
    {
        return $this->belongsTo(Sach::class, 'id_sach');
    }
}