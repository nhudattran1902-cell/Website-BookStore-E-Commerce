<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrangSach extends Model
{
    protected $table = 'trang_sach';

    const CREATED_AT = 'ngay_tao';
    public $timestamps = false;
    const UPDATED_AT = 'ngay_cap_nhat';

    protected $fillable = [
        'id_sach',
        'so_trang',
        'duong_dan_anh',
        'cho_phep_doc_thu',
    ];

    protected $casts = [
        'cho_phep_doc_thu' => 'boolean',
        'so_trang' => 'integer',
    ];

    public function sach()
    {
        return $this->belongsTo(Sach::class, 'id_sach');
    }
}