<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TinNhanChat extends Model
{
    use HasFactory;

    public const KENH_CHATBOT = 'chatbot';

    public const KENH_CSKH = 'cskh';

    protected $table = 'tin_nhan_chat';

    const CREATED_AT = 'ngay_tao';

    const UPDATED_AT = 'ngay_cap_nhat';

    protected $fillable = [
        'id_nguoi_dung',
        'session_id',
        'kenh',
        'ten_khach',
        'id_admin',
        'nguoi_gui',
        'noi_dung',
        'da_doc',
    ];

    public function scopeKenh(Builder $query, string $kenh): Builder
    {
        return $query->where('kenh', $kenh);
    }

    public function nguoiDung(): BelongsTo
    {
        return $this->belongsTo(NguoiDung::class, 'id_nguoi_dung');
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(NguoiDung::class, 'id_admin');
    }
}
