<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TinNhanChat extends Model
{
    use HasFactory;

    protected $table = 'tin_nhan_chat';

    const CREATED_AT = 'ngay_tao';

    const UPDATED_AT = 'ngay_cap_nhat';

    protected $fillable = [
        'id_nguoi_dung',
        'session_id',
        'id_admin',
        'nguoi_gui',
        'noi_dung',
        'da_doc',
    ];

    public function nguoiDung()
    {
        return $this->belongsTo(NguoiDung::class, 'id_nguoi_dung');
    }

    public function admin()
    {
        return $this->belongsTo(NguoiDung::class, 'id_admin');
    }
}
