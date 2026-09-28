<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NguoiDung;

class UserController extends Controller
{
    public function index()
    {
        $danhSachNguoiDung = NguoiDung::latest()->paginate(10);
        return view('admin.users.index', compact('danhSachNguoiDung'));
    }
}
