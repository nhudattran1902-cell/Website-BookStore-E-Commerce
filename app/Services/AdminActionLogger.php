<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminActionLogger
{
    /** @param array<string, mixed> $data */
    public function log(
        Request $request,
        string $action,
        string $subjectType,
        int|string|null $subjectId = null,
        array $data = [],
    ): void {
        DB::table('admin_action_logs')->insert([
            'id_nguoi_thuc_hien' => $request->user()?->id,
            'hanh_dong' => $action,
            'doi_tuong' => $subjectType,
            'doi_tuong_id' => $subjectId === null ? null : (string) $subjectId,
            'du_lieu' => $data === [] ? null : json_encode($data, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_at' => now(),
        ]);
    }
}
