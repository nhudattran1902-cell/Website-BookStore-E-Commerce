<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TheoDoiHang extends Model
{
    protected $table = 'theo_doi_hang';

    const CREATED_AT = 'ngay_tao';

    const UPDATED_AT = 'ngay_cap_nhat';

    protected $fillable = [
        'id_nguoi_dung',
        'id_sach',
        'da_thong_bao_at',
    ];

    protected function casts(): array
    {
        return [
            'da_thong_bao_at' => 'datetime',
        ];
    }

    public function nguoiDung(): BelongsTo
    {
        return $this->belongsTo(NguoiDung::class, 'id_nguoi_dung');
    }

    public function sach(): BelongsTo
    {
        return $this->belongsTo(Sach::class, 'id_sach');
    }
}
