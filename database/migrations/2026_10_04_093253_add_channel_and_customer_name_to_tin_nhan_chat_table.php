<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tin_nhan_chat', function (Blueprint $table) {
            $table->string('kenh', 20)->default('cskh')->after('session_id')->index();
            $table->string('ten_khach', 150)->nullable()->after('kenh');
        });

        $automatedReplies = DB::table('tin_nhan_chat')
            ->where('nguoi_gui', 'admin')
            ->whereNull('id_admin')
            ->orderBy('id')
            ->get(['id', 'id_nguoi_dung', 'session_id']);

        foreach ($automatedReplies as $reply) {
            DB::table('tin_nhan_chat')->where('id', $reply->id)->update(['kenh' => 'chatbot']);

            DB::table('tin_nhan_chat')
                ->where('nguoi_gui', 'khach_hang')
                ->where('id', '<', $reply->id)
                ->when(
                    $reply->id_nguoi_dung !== null,
                    fn ($query) => $query->where('id_nguoi_dung', $reply->id_nguoi_dung),
                    fn ($query) => $query->whereNull('id_nguoi_dung')->where('session_id', $reply->session_id),
                )
                ->orderByDesc('id')
                ->limit(1)
                ->update(['kenh' => 'chatbot']);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tin_nhan_chat', function (Blueprint $table) {
            $table->dropIndex(['kenh']);
            $table->dropColumn(['kenh', 'ten_khach']);
        });
    }
};
