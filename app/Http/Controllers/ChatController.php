<?php

namespace App\Http\Controllers;

use App\Models\TinNhanChat;
use App\Services\BookshopAssistant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ChatController extends Controller
{
    protected function conversationQuery(string $conversationId, string $channel): Builder
    {
        return TinNhanChat::query()
            ->kenh($channel)
            ->when(
                is_numeric($conversationId),
                fn (Builder $query): Builder => $query->where('id_nguoi_dung', (int) $conversationId),
                fn (Builder $query): Builder => $query->where('session_id', $conversationId),
            );
    }

    protected function customerMessagesQuery(Request $request, string $channel): Builder
    {
        $userId = Auth::id();

        return TinNhanChat::query()
            ->kenh($channel)
            ->when(
                $userId !== null,
                fn (Builder $query): Builder => $query->where('id_nguoi_dung', $userId),
                fn (Builder $query): Builder => $query
                    ->whereNull('id_nguoi_dung')
                    ->where('session_id', $request->session()->getId()),
            );
    }

    public function sendContactMessage(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ten_lien_he' => ['required', 'string', 'max:150'],
            'email_lien_he' => ['required', 'email', 'max:255'],
            'noi_dung' => ['required', 'string', 'max:2000'],
        ]);

        $userId = Auth::id();
        $message = "Yêu cầu liên hệ từ trang Liên hệ\n"
            .'Email: '.$validated['email_lien_he']."\n\n"
            .$validated['noi_dung'];

        TinNhanChat::create([
            'id_nguoi_dung' => $userId,
            'session_id' => $userId === null ? $request->session()->getId() : null,
            'kenh' => TinNhanChat::KENH_CSKH,
            'ten_khach' => $validated['ten_lien_he'],
            'nguoi_gui' => 'khach_hang',
            'noi_dung' => $message,
        ]);

        return redirect()->route('pages.contact')
            ->with('contact_success', 'Đã gửi liên hệ. BOOK & BOX sẽ phản hồi trong mục CSKH.');
    }

    public function getChatbotMessages(Request $request): JsonResponse
    {
        $messages = $this->customerMessagesQuery($request, TinNhanChat::KENH_CHATBOT)
            ->orderBy('ngay_tao')
            ->get();

        return response()->json(['messages' => $messages]);
    }

    public function sendChatbotMessage(Request $request, BookshopAssistant $assistant): JsonResponse
    {
        $validated = $request->validate([
            'noi_dung' => ['required', 'string', 'max:1000'],
        ]);

        $userId = Auth::id();
        $sessionId = $userId === null ? $request->session()->getId() : null;
        $conversation = $this->customerMessagesQuery($request, TinNhanChat::KENH_CHATBOT);
        $history = (clone $conversation)
            ->latest('id')
            ->limit(6)
            ->get()
            ->reverse()
            ->map(fn (TinNhanChat $message): array => [
                'role' => $message->nguoi_gui === 'khach_hang' ? 'user' : 'assistant',
                'content' => $message->noi_dung,
            ])
            ->values()
            ->all();

        $message = TinNhanChat::create([
            'id_nguoi_dung' => $userId,
            'session_id' => $sessionId,
            'kenh' => TinNhanChat::KENH_CHATBOT,
            'ten_khach' => Auth::user()?->ho_ten,
            'nguoi_gui' => 'khach_hang',
            'noi_dung' => $validated['noi_dung'],
        ]);

        $answer = $assistant->reply($validated['noi_dung'], $history, $userId)
            ?? 'Mình chưa thể trả lời lúc này. Bạn hãy chuyển sang mục CSKH để nhân viên hỗ trợ.';

        $reply = TinNhanChat::create([
            'id_nguoi_dung' => $userId,
            'session_id' => $sessionId,
            'kenh' => TinNhanChat::KENH_CHATBOT,
            'ten_khach' => Auth::user()?->ho_ten,
            'nguoi_gui' => 'admin',
            'noi_dung' => $answer,
            'da_doc' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => $message,
            'reply' => $reply,
            'ai_enabled' => $assistant->isConfigured(),
        ]);
    }

    public function getSupportMessages(Request $request): JsonResponse
    {
        $messages = $this->customerMessagesQuery($request, TinNhanChat::KENH_CSKH)
            ->orderBy('ngay_tao')
            ->get();

        return response()->json(['messages' => $messages]);
    }

    public function sendSupportMessage(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ten_khach' => [Rule::requiredIf(Auth::guest()), 'nullable', 'string', 'max:150'],
            'noi_dung' => ['required', 'string', 'max:1000'],
        ]);

        $userId = Auth::id();
        $message = TinNhanChat::create([
            'id_nguoi_dung' => $userId,
            'session_id' => $userId === null ? $request->session()->getId() : null,
            'kenh' => TinNhanChat::KENH_CSKH,
            'ten_khach' => Auth::user()?->ho_ten ?? ($validated['ten_khach'] ?? null),
            'nguoi_gui' => 'khach_hang',
            'noi_dung' => $validated['noi_dung'],
        ]);

        return response()->json([
            'success' => true,
            'message' => $message,
        ]);
    }

    public function adminIndex(Request $request): View
    {
        $conversations = $this->supportConversations()->get();
        $activeConv = $request->query('conv_id');
        $messages = collect();

        if (is_string($activeConv) && $activeConv !== '') {
            $conversation = $this->conversationQuery($activeConv, TinNhanChat::KENH_CSKH);
            $messages = (clone $conversation)->orderBy('ngay_tao')->get();

            (clone $conversation)
                ->where('nguoi_gui', 'khach_hang')
                ->update(['da_doc' => true]);
        }

        $activeCustomerName = $this->customerName(
            $conversations->first(fn (TinNhanChat $conversation): bool => (string) $conversation->conv_id === (string) $activeConv)
        );

        return view('admin.chat.index', compact('conversations', 'messages', 'activeConv', 'activeCustomerName'));
    }

    public function adminUpdates(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'conv_id' => ['nullable', 'string', 'max:100'],
            'after_id' => ['nullable', 'integer', 'min:0'],
        ]);

        $activeConv = $filters['conv_id'] ?? null;
        $messages = collect();

        if (is_string($activeConv) && $activeConv !== '') {
            $conversation = $this->conversationQuery($activeConv, TinNhanChat::KENH_CSKH);
            $messages = (clone $conversation)
                ->when($filters['after_id'] ?? null, fn (Builder $query, int $afterId): Builder => $query->where('id', '>', $afterId))
                ->orderBy('id')
                ->get();

            (clone $conversation)
                ->where('nguoi_gui', 'khach_hang')
                ->where('da_doc', false)
                ->update(['da_doc' => true]);
        }

        $conversations = $this->supportConversations()
            ->get()
            ->map(fn (TinNhanChat $conversation): array => [
                'id' => (string) $conversation->conv_id,
                'name' => $this->customerName($conversation),
                'last_msg_time' => $conversation->last_msg_time,
                'unread_count' => (int) $conversation->unread_count,
            ]);

        return response()->json([
            'conversations' => $conversations,
            'messages' => $messages,
        ]);
    }

    public function adminReply(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'conv_id' => ['required', 'string', 'max:100'],
            'noi_dung' => ['required', 'string', 'max:1000'],
        ]);

        $isUserConversation = is_numeric($validated['conv_id']);
        $message = TinNhanChat::create([
            'id_nguoi_dung' => $isUserConversation ? (int) $validated['conv_id'] : null,
            'session_id' => $isUserConversation ? null : $validated['conv_id'],
            'kenh' => TinNhanChat::KENH_CSKH,
            'id_admin' => Auth::id(),
            'nguoi_gui' => 'admin',
            'noi_dung' => $validated['noi_dung'],
            'da_doc' => true,
        ]);

        return response()->json(['success' => true, 'message' => $message]);
    }

    private function supportConversations(): Builder
    {
        return TinNhanChat::query()
            ->kenh(TinNhanChat::KENH_CSKH)
            ->with('nguoiDung:id,ho_ten,email')
            ->selectRaw('COALESCE(CAST(id_nguoi_dung AS CHAR), session_id) as conv_id, id_nguoi_dung, session_id, MAX(ten_khach) as ten_khach, MAX(ngay_tao) as last_msg_time, SUM(CASE WHEN nguoi_gui = ? AND da_doc = ? THEN 1 ELSE 0 END) as unread_count', ['khach_hang', false])
            ->groupBy('conv_id', 'id_nguoi_dung', 'session_id')
            ->orderByDesc('last_msg_time');
    }

    private function customerName(?TinNhanChat $conversation): string
    {
        return $conversation?->nguoiDung?->ho_ten
            ?? $conversation?->ten_khach
            ?? 'Khách vãng lai';
    }
}
