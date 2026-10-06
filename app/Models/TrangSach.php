<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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

    public function getDuongDanAnhUrlAttribute(): ?string
    {
        $imagePath = $this->duong_dan_anh;

        if (! is_string($imagePath) || $imagePath === '') {
            return null;
        }

        if (Str::startsWith($imagePath, ['http://', 'https://'])) {
            return $imagePath;
        }

        return Storage::disk('public')->url(ltrim($imagePath, '/'));
    }

    public function sach()
    {
        return $this->belongsTo(Sach::class, 'id_sach');
    }
}
