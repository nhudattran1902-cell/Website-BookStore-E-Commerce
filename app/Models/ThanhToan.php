<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ThanhToan extends Model
{
    protected $table = 'thanh_toan';

    const CREATED_AT = 'ngay_tao';
    public $timestamps = false;

    protected $fillable = [
        'id_don_hang',
        'phuong_thuc_thanh_toan',
        'ma_giao_dich',
        'so_tien',
        'trang_thai',
        'ngay_thanh_toan',
    ];

    public function donHang()
    {
        return $this->belongsTo(DonHang::class, 'id_don_hang');
    }
}