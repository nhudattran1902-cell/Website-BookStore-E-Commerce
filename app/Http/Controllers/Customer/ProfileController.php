<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    // Hiển thị trang hồ sơ cá nhân
    public function index()
    {
        $user = Auth::user();
        return view('customer.profile', compact('user'));
    }

    // Cập nhật thông tin (Họ tên, SĐT)
    public function update(Request $request)
    {
        $request->validate([
            'ho_ten' => 'required|string|max:255',
            'so_dien_thoai' => 'nullable|string|max:20',
        ]);

        $user = Auth::user();
        $user->ho_ten = $request->ho_ten;
        $user->so_dien_thoai = $request->so_dien_thoai;
        $user->save();

        return redirect()->back()->with('success', 'Cập nhật thông tin thành công!');
    }

    // Cập nhật mật khẩu
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|string|min:6|confirmed',
        ]);

        $user = Auth::user();

        // Kiểm tra mật khẩu hiện tại có khớp không
        if (!Hash::check($request->current_password, $user->mat_khau)) {
            return redirect()->back()->with('error', 'Mật khẩu hiện tại không chính xác!');
        }

        // Cập nhật mật khẩu mới (đã mã hóa)
        $user->mat_khau = Hash::make($request->new_password);
        $user->save();

        return redirect()->back()->with('success', 'Đổi mật khẩu thành công!');
    }
}