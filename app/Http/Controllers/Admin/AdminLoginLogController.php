<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminLoginLogController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'email' => ['nullable', 'string', 'max:150'],
            'ket_qua' => ['nullable', 'in:password_accepted,password_failed,locked'],
        ]);

        $loginLogsQuery = DB::table('admin_login_logs');

        if (filled($filters['email'] ?? null)) {
            $loginLogsQuery->where('email', 'like', '%'.trim($filters['email']).'%');
        }

        if (filled($filters['ket_qua'] ?? null)) {
            $loginLogsQuery->where('ket_qua', $filters['ket_qua']);
        }

        $loginLogs = $loginLogsQuery
            ->orderByDesc('attempted_at')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.login-logs.index', compact('loginLogs'));
    }
}
