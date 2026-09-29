<?php

namespace App\Http\Controllers;

use App\Models\TinNhan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    // Khách hàng: Lấy danh sách tin nhắn của phiên làm việc
    public function getCustomerMessages(Request $request)
    {
        $userId = Auth::id();
        $sessionId = session()->getId();

        $messages = TinNhan::query()
            ->when($userId, fn ($q) => $q->where('id_nguoi_dung', $userId))
            ->when(! $userId, fn ($q) => $q->where('session_id', $sessionId))
            ->orderBy('ngay_tao', 'asc')
            ->get();

        return response()->json(['messages' => $messages]);
    }

    // Khách hàng: Gửi tin nhắn
    public function sendCustomerMessage(Request $request)
    {
        $request->validate([
            'noi_dung' => 'required|string|max:1000',
        ]);

        $message = TinNhan::create([
            'id_nguoi_dung' => Auth::id(),
            'session_id' => Auth::check() ? null : session()->getId(),
            'nguoi_gui' => 'khach_hang',
            'noi_dung' => $request->noi_dung,
        ]);

        return response()->json(['success' => true, 'message' => $message]);
    }

    // Admin: Màn hình chính quản lý Chat
    public function adminIndex(Request $request)
    {
        // Nhóm hội thoại theo id_nguoi_dung hoặc session_id
        $conversations = TinNhan::with('nguoiDung')
            ->selectRaw('COALESCE(CAST(id_nguoi_dung AS CHAR), session_id) as conv_id, id_nguoi_dung, session_id, MAX(ngay_tao) as last_msg_time')
            ->groupBy('conv_id', 'id_nguoi_dung', 'session_id')
            ->orderBy('last_msg_time', 'desc')
            ->get();

        $activeConv = $request->query('conv_id');
        $messages = collect();

        if ($activeConv) {
            $messages = TinNhan::where('id_nguoi_dung', $activeConv)
                ->orWhere('session_id', $activeConv)
                ->orderBy('ngay_tao', 'asc')
                ->get();

            // Đánh dấu đã đọc đối với các tin nhắn của khách
            TinNhan::where(function ($q) use ($activeConv) {
                $q->where('id_nguoi_dung', $activeConv)->orWhere('session_id', $activeConv);
            })->where('nguoi_gui', 'khach_hang')->update(['da_doc' => true]);
        }

        return view('admin.chat.index', compact('conversations', 'messages', 'activeConv'));
    }

    // Admin: Gửi tin nhắn trả lời
    public function adminReply(Request $request)
    {
        $request->validate([
            'conv_id' => 'required|string',
            'noi_dung' => 'required|string|max:1000',
        ]);

        $convId = $request->conv_id;
        $isNumeric = is_numeric($convId);

        $message = TinNhan::create([
            'id_nguoi_dung' => $isNumeric ? $convId : null,
            'session_id' => ! $isNumeric ? $convId : null,
            'id_admin' => Auth::id(),
            'nguoi_gui' => 'admin',
            'noi_dung' => $request->noi_dung,
            'da_doc' => true,
        ]);

        return response()->json(['success' => true, 'message' => $message]);
    }
}
