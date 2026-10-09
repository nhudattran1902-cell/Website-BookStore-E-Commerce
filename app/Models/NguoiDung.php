<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class NguoiDung extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    public const STAFF_ROLES = ['admin', 'cskh', 'nhan_vien_kho', 'ke_toan'];

    protected $table = 'nguoi_dung';

    protected $attributes = [
        'dang_hoat_dong' => true,
    ];

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
        'registration_otp_hash',
    ];

    protected function casts(): array
    {
        return [
            'admin_totp_secret' => 'encrypted',
            'admin_totp_confirmed_at' => 'datetime',
            'admin_email_otp_expires_at' => 'datetime',
            'admin_email_otp_attempts' => 'integer',
            'admin_password_reset_otp_expires_at' => 'datetime',
            'admin_password_reset_otp_attempts' => 'integer',
            'registration_otp_expires_at' => 'datetime',
            'registration_otp_attempts' => 'integer',
            'email_verified_at' => 'datetime',
            'dang_hoat_dong' => 'boolean',
            'quyen_han' => 'array',
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

    public function hasAnyRole(string ...$roleNames): bool
    {
        return $this->vaiTro()->whereIn('ten_vai_tro', $roleNames)->exists();
    }

    public function isStaffMember(): bool
    {
        return $this->hasAnyRole(...self::STAFF_ROLES);
    }

    public function hasPermission(string $permission): bool
    {
        if ($this->hasRole('admin')) {
            return true;
        }

        $assignedPermissions = $this->quyen_han;

        if (is_array($assignedPermissions)) {
            return in_array($permission, $assignedPermissions, true);
        }

        $roleNames = $this->relationLoaded('vaiTro')
            ? $this->vaiTro->pluck('ten_vai_tro')
            : $this->vaiTro()->pluck('ten_vai_tro');

        foreach ($roleNames as $roleName) {
            if (in_array($permission, config("admin_permissions.role_defaults.{$roleName}", []), true)) {
                return true;
            }
        }

        return false;
    }

    public function vaiTro(): BelongsToMany
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

    public function sachYeuThich(): BelongsToMany
    {
        return $this->belongsToMany(Sach::class, 'sach_yeu_thich', 'id_nguoi_dung', 'id_sach')
            ->withPivot('ngay_tao');
    }

    public function theoDoiHang(): HasMany
    {
        return $this->hasMany(TheoDoiHang::class, 'id_nguoi_dung');
    }
}
