<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class NguoiDung extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable;

    protected $table = 'nguoi_dung';

    // Chỉ định tên 2 cột Timestamps tiếng Việt trong CSDL
    const CREATED_AT = 'ngay_tao';

    const UPDATED_AT = 'ngay_cap_nhat';

    protected $fillable = [
        'user_id',
        'ho_ten',
        'email',
        'mat_khau',
        'so_dien_thoai',
        'anh_dai_dien',
        'dia_chi_mac_dinh',
    ];

    protected $hidden = [
        'mat_khau',
        'chuoi_nho_dang_nhap',
        'admin_totp_secret',
        'admin_email_otp_hash',
    ];

    protected function casts(): array
    {
        return [
            'admin_totp_secret' => 'encrypted',
            'admin_totp_confirmed_at' => 'datetime',
            'admin_email_otp_expires_at' => 'datetime',
            'admin_email_otp_attempts' => 'integer',
            'email_verified_at' => 'datetime',
        ];
    }

    public function getAnhDaiDienUrlAttribute(): ?string
    {
        $avatarPath = $this->anh_dai_dien;

        if (! is_string($avatarPath) || $avatarPath === '') {
            return null;
        }

        if (Str::startsWith($avatarPath, ['http://', 'https://'])) {
            return $avatarPath;
        }

        return Storage::disk('public')->url(ltrim($avatarPath, '/'));
    }

    /**
     * Override cột mật khẩu đăng nhập của Laravel
     */
    public function getAuthPassword()
    {
        return $this->mat_khau;
    }

    /**
     * Override cột ghi nhớ đăng nhập (Remember Me)
     */
    public function getRememberTokenName()
    {
        return 'chuoi_nho_dang_nhap';
    }

    /**
     * Kiểm tra vai trò người dùng (Admin/User)
     */
    public function hasRole(string $roleName): bool
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

    public function danhGia(): HasMany
    {
        return $this->hasMany(DanhGiaSach::class, 'id_nguoi_dung');
    }
}
