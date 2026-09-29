<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Sach extends Model
{
    protected $table = 'sach';

    const CREATED_AT = 'ngay_tao';
    const UPDATED_AT = 'ngay_cap_nhat';

    protected $fillable = [
        'tieu_de',
        'duong_dan_tinh',
        'id_the_loai',
        'id_nha_xuat_ban',
        'gia_ban',
        'gia_khuyen_mai',
        'ma_isbn',
        'anh_bia',
        'mo_ta',
        'nam_xuat_ban',
        'noi_bat',
        'ban_chay',
        'dang_hoat_dong',
    ];

    // Quan hệ Nhiều - Nhiều: Một cuốn sách có thể do nhiều Tác giả sáng tác
    public function tacGia(): BelongsToMany
    {
        return $this->belongsToMany(TacGia::class, 'sach_tac_gia', 'id_sach', 'id_tac_gia');
    }

    // Alias hỗ trợ nếu ở đâu đó gọi $book->tacGias
    public function tacGias(): BelongsToMany
    {
        return $this->tacGia();
    }

    // Quan hệ: Sách thuộc 1 Thể loại
    public function theLoai(): BelongsTo
    {
        return $this->belongsTo(TheLoai::class, 'id_the_loai');
    }

    // Quan hệ: Sách thuộc 1 Nhà xuất bản
    public function nhaXuatBan(): BelongsTo
    {
        return $this->belongsTo(NhaXuatBan::class, 'id_nha_xuat_ban');
    }

    // Quan hệ: Sách có nhiều Trang sách (đọc thử)
    public function trangSach(): HasMany
    {
        return $this->hasMany(TrangSach::class, 'id_sach');
    }

    // Quan hệ: Sách có 1 Kho hàng
    public function khoHang(): HasOne
    {
        return $this->hasOne(KhoHang::class, 'id_sach');
    }

    // Quan hệ: Các hình ảnh phụ của sách
    public function hinhAnh(): HasMany
    {
        return $this->hasMany(HinhAnhSach::class, 'id_sach');
    }

    // Quan hệ: Đánh giá của sách
    public function danhGia(): HasMany
    {
        return $this->hasMany(DanhGiaSach::class, 'id_sach');
    }
}