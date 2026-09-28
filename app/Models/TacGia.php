<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TacGia extends Model
{
    protected $table = 'tac_gia';
    public $timestamps = false;

    protected $fillable = [
        'ten_tac_gia',
        'tieu_su',
        'anh_dai_dien',
    ];

    public function sach()
    {
        return $this->hasMany(Sach::class, 'id_tac_gia');
    }
}