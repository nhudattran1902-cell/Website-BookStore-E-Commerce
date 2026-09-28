<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class NguoiDung extends Authenticatable
{
    use Notifiable;

    protected $table = 'nguoi_dung'; // Chỉ định đúng tên bảng trong CSDL nha_sach_db

    // 1. Khai báo lại tên 2 cột timestamps tiếng Việt trong CSDL nha_sach_db
    const CREATED_AT = 'ngay_tao';
    const UPDATED_AT = 'ngay_cap_nhat';

    protected $fillable = [
        'ho_ten',
        'email',
        'mat_khau',
        'so_dien_thoai',
        'anh_dai_dien',
    ];

    protected $hidden = [
        'mat_khau',
        'chuoi_nho_dang_nhap',
    ];

    // Chỉ định lại tên cột mật khẩu (do mặc định Laravel dùng 'password')
    public function getAuthPassword()
    {
        return $this->mat_khau;
    }

    // Kiểm tra vai trò Admin
    public function hasRole($roleName)
    {
        return $this->vaiTro()->where('ten_vai_tro', $roleName)->exists();
    }

    public function vaiTro()
    {
        return $this->belongsToMany(
            VaiTro::class,
            'vai_tro_nguoi_dung',
            'id_nguoi_dung',
            'id_vai_tro'
        );
    }
}
