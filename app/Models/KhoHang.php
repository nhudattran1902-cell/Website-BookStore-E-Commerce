<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KhoHang extends Model
{
    protected $table = 'kho_hang';

    // Bảng kho_hang chỉ sử dụng cột ngay_cap_nhat làm timestamps
    const CREATED_AT = null;

    const UPDATED_AT = 'ngay_cap_nhat';

    protected $fillable = [
        'id_sach',
        'so_luong_ton',
        'so_luong_dat_truoc',
        'nguong_canh_bao',
        'khu_vuc',
        'ke_hang',
        'o_chua',
    ];

    /**
     * Mối quan hệ: Kho hàng thuộc về 1 cuốn Sách
     */
    public function sach(): BelongsTo
    {
        return $this->belongsTo(Sach::class, 'id_sach');
    }

    /**
     * Accessor: Tồn kho khả dụng thực tế để khách đặt mua (Tồn kho trừ Giữ chỗ)
     */
    public function getSoLuongKhaDungAttribute(): int
    {
        return max(0, $this->so_luong_ton - $this->so_luong_dat_truoc);
    }
}
