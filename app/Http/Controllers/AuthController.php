<?php

namespace App\Http\Controllers;

use App\Models\NguoiDung;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // Hiển thị form đăng nhập
    public function showLogin()
    {
        return view('auth.login');
    }

    // Xử lý Đăng nhập
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'mat_khau' => 'required',
        ]);

        // Kiểm tra người dùng theo email trong bảng nguoi_dung
        $user = NguoiDung::where('email', $credentials['email'])->first();

        if ($user && Hash::check($credentials['mat_khau'], $user->mat_khau)) {
            Auth::login($user, $request->remember);
            $request->session()->regenerate();

            // Nếu là Admin thì chuyển hướng tới Dashboard, ngược lại về Trang chủ
            if ($user->hasRole('admin')) {
                return redirect()->intended('/admin/dashboard');
            }

            return redirect()->intended('/');
        }

        return back()->withErrors([
            'email' => 'Thông tin đăng nhập không chính xác.',
        ])->onlyInput('email');
    }

    // Hiển thị form đăng ký
    public function showRegister()
    {
        return view('auth.register');
    }

    // Xử lý Đăng ký tài khoản khách hàng
    public function register(Request $request)
    {
        $request->validate([
            'ho_ten' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:nguoi_dung,email',
            'mat_khau' => 'required|string|min:6|confirmed',
            'so_dien_thoai' => 'nullable|string|max:20',
        ]);

        $user = NguoiDung::create([
            'ho_ten' => $request->ho_ten,
            'email' => $request->email,
            'mat_khau' => Hash::make($request->mat_khau),
            'so_dien_thoai' => $request->so_dien_thoai,
        ]);

        Auth::login($user);

        return redirect('/')->with('success', 'Đăng ký tài khoản thành công!');
    }

    // Đăng xuất
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
