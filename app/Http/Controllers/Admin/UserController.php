<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NguoiDung;
use App\Models\VaiTro;
use App\Services\AdminActionLogger;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $trangThai = $request->query('status', 'all');
        $danhSachNguoiDung = NguoiDung::withTrashed()
            ->with('vaiTro')
            ->when($trangThai === 'active', fn ($query) => $query->where('dang_hoat_dong', true)->whereNull('deleted_at'))
            ->when($trangThai === 'inactive', fn ($query) => $query->where('dang_hoat_dong', false)->whereNull('deleted_at'))
            ->when($trangThai === 'deleted', fn ($query) => $query->onlyTrashed())
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.users.index', compact('danhSachNguoiDung'));
    }

    public function updateRole(Request $request, NguoiDung $user, AdminActionLogger $actionLogger): RedirectResponse
    {
        if ($user->hasRole('admin') || $user->id === Auth::id()) {
            return back()->with('error', 'Không thể thay đổi vai trò tài khoản quản trị đang được bảo vệ.');
        }

        $oldRoles = $user->vaiTro()->pluck('ten_vai_tro')->all();
        $data = $request->validate([
            'role' => ['required', Rule::in(['customer', 'cskh', 'nhan_vien_kho', 'ke_toan'])],
        ]);

        $roleDescriptions = [
            'customer' => 'Khách hàng mua sách',
            'cskh' => 'Nhân viên chăm sóc khách hàng',
            'nhan_vien_kho' => 'Nhân viên quản lý kho',
            'ke_toan' => 'Nhân viên kế toán',
        ];
        $role = VaiTro::firstOrCreate(
            ['ten_vai_tro' => $data['role']],
            ['mo_ta' => $roleDescriptions[$data['role']]],
        );

        $user->vaiTro()->sync([$role->id]);
        $user->quyen_han = null;
        $user->save();
        $actionLogger->log($request, 'user.role.updated', 'nguoi_dung', $user->id, [
            'old_roles' => $oldRoles,
            'new_role' => $data['role'],
        ]);

        return back()->with('success', 'Đã cập nhật vai trò tài khoản.');
    }

    public function editPermissions(NguoiDung $user): View|RedirectResponse
    {
        if ($user->hasRole('admin') || ! $user->isStaffMember()) {
            return back()->with('error', 'Chỉ có thể phân quyền chi tiết cho tài khoản nhân viên.');
        }

        $permissionGroups = config('admin_permissions.groups');
        $allPermissions = collect($permissionGroups)
            ->flatMap(fn (array $group): array => array_column($group['permissions'], 'key'))
            ->all();
        $selectedPermissions = is_array($user->quyen_han)
            ? $user->quyen_han
            : array_values(array_filter($allPermissions, fn (string $permission): bool => $user->hasPermission($permission)));

        return view('admin.users.permissions', compact('user', 'permissionGroups', 'selectedPermissions'));
    }

    public function updatePermissions(
        Request $request,
        NguoiDung $user,
        AdminActionLogger $actionLogger,
    ): RedirectResponse {
        if ($user->hasRole('admin') || ! $user->isStaffMember()) {
            return back()->with('error', 'Chỉ có thể phân quyền chi tiết cho tài khoản nhân viên.');
        }

        $allPermissions = collect(config('admin_permissions.groups'))
            ->flatMap(fn (array $group): array => array_column($group['permissions'], 'key'))
            ->all();
        $data = $request->validate([
            'permissions' => ['required', 'array', 'min:1'],
            'permissions.*' => ['required', 'string', Rule::in($allPermissions)],
        ]);

        $oldPermissions = $user->quyen_han ?? array_values(array_filter(
            $allPermissions,
            fn (string $permission): bool => $user->hasPermission($permission),
        ));
        $newPermissions = array_values(array_intersect($allPermissions, array_unique($data['permissions'])));
        $dependencies = [
            'orders.status.update' => ['orders.view'],
            'orders.shipping.update' => ['orders.view'],
            'inventory.update' => ['inventory.view'],
            'inventory.imports.view' => ['inventory.view'],
            'inventory.import' => ['inventory.view', 'inventory.imports.view'],
            'payments.confirm' => ['payments.view'],
            'chat.reply' => ['chat.view'],
            'reviews.moderate' => ['reviews.view'],
            'reviews.delete' => ['reviews.view'],
        ];
        foreach ($dependencies as $permission => $requiredPermissions) {
            if (in_array($permission, $newPermissions, true)) {
                $newPermissions = array_merge($newPermissions, $requiredPermissions);
            }
        }
        $newPermissions = array_values(array_unique($newPermissions));
        $user->quyen_han = $newPermissions;
        $user->save();

        $actionLogger->log($request, 'user.permissions.updated', 'nguoi_dung', $user->id, [
            'old_permissions' => $oldPermissions,
            'new_permissions' => $newPermissions,
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', 'Đã cập nhật quyền chi tiết cho nhân viên.');
    }

    public function toggleStatus(NguoiDung $user): RedirectResponse
    {
        if ($this->isProtectedAdmin($user)) {
            return back()->with('error', 'Không thể thay đổi trạng thái tài khoản quản trị viên.');
        }

        if ($user->id === Auth::id()) {
            return back()->with('error', 'Bạn không thể vô hiệu hóa tài khoản đang đăng nhập.');
        }

        $user->dang_hoat_dong = ! $user->dang_hoat_dong;
        $user->save();

        return back()->with('success', $user->dang_hoat_dong
            ? 'Đã kích hoạt tài khoản người dùng.'
            : 'Đã vô hiệu hóa tài khoản người dùng.');
    }

    public function destroy(NguoiDung $user): RedirectResponse
    {
        if ($this->isProtectedAdmin($user)) {
            return back()->with('error', 'Không thể xóa tài khoản quản trị viên.');
        }

        if ($user->id === Auth::id()) {
            return back()->with('error', 'Bạn không thể xóa tài khoản đang đăng nhập.');
        }

        $user->delete();

        return back()->with('success', 'Đã xóa mềm tài khoản. Lịch sử đơn hàng và nhật ký vẫn được giữ lại.');
    }

    public function restore(int $id): RedirectResponse
    {
        $user = NguoiDung::withTrashed()->findOrFail($id);
        $user->restore();

        return back()->with('success', 'Đã khôi phục tài khoản người dùng.');
    }

    private function isProtectedAdmin(NguoiDung $user): bool
    {
        return $user->hasRole('admin');
    }
}
