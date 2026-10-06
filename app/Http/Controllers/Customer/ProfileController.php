<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();

        return view('customer.profile', compact('user'));
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'ho_ten' => ['required', 'string', 'max:255'],
            'so_dien_thoai' => ['nullable', 'string', 'max:20'],
            'anh_dai_dien' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $user = Auth::user();
        $previousAvatarPath = $user->anh_dai_dien;
        $newAvatarPath = null;
        $user->ho_ten = $data['ho_ten'];
        $user->so_dien_thoai = $data['so_dien_thoai'] ?? null;

        if ($request->hasFile('anh_dai_dien')) {
            $newAvatarPath = $request->file('anh_dai_dien')->store('avatars', 'public');

            if ($newAvatarPath === false) {
                return back()
                    ->withErrors(['anh_dai_dien' => 'Không thể lưu ảnh đại diện. Vui lòng thử lại.'])
                    ->withInput();
            }

            $user->anh_dai_dien = $newAvatarPath;
        }

        $user->save();

        if (
            $newAvatarPath !== null
            && is_string($previousAvatarPath)
            && str_starts_with($previousAvatarPath, 'avatars/')
            && Storage::disk('public')->exists($previousAvatarPath)
        ) {
            Storage::disk('public')->delete($previousAvatarPath);
        }

        return back()->with('success', 'Cập nhật thông tin thành công!');
    }

    public function changePassword(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|string|min:6|confirmed',
        ]);

        $user = Auth::user();

        if (! Hash::check($request->current_password, $user->mat_khau)) {
            return back()->with('error', 'Mật khẩu hiện tại không chính xác!');
        }

        $user->mat_khau = Hash::make($request->new_password);
        $user->save();

        return back()->with('success', 'Đổi mật khẩu thành công!');
    }
}
