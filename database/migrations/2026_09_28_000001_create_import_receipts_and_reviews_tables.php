<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Thêm các cột Vị trí kho (Khu vực, Kệ, Ô) vào bảng kho_hang
        if (Schema::hasTable('kho_hang')) {
            Schema::table('kho_hang', function (Blueprint $table) {
                if (! Schema::hasColumn('kho_hang', 'khu_vuc')) {
                    $table->string('khu_vuc', 50)->nullable()->comment('Khu vực kho, VD: Khu A, Khu B');
                }
                if (! Schema::hasColumn('kho_hang', 'ke_hang')) {
                    $table->string('ke_hang', 50)->nullable()->comment('Kệ hàng, VD: Kệ 01, Kệ 02');
                }
                if (! Schema::hasColumn('kho_hang', 'o_chua')) {
                    $table->string('o_chua', 50)->nullable()->comment('Ô chứa, VD: Ô A1-02');
                }
            });
        }

        // 2. Tạo bảng Phiếu nhập kho (phieu_nhap_kho) nếu chưa tồn tại
        if (! Schema::hasTable('phieu_nhap_kho')) {
            Schema::create('phieu_nhap_kho', function (Blueprint $table) {
                $table->id();
                $table->string('ma_phieu', 50)->unique();
                $table->foreignId('id_nha_xuat_ban')->nullable()->constrained('nha_xuat_ban')->onDelete('set null');
                $table->foreignId('id_nguoi_nhap')->constrained('nguoi_dung')->onDelete('cascade');
                $table->decimal('tong_tien', 14, 0)->default(0);
                $table->text('ghi_chu')->nullable();
                $table->timestamp('ngay_tao')->useCurrent();
                $table->timestamp('ngay_cap_nhat')->useCurrent()->useCurrentOnUpdate();
            });
        }

        // 3. Tạo bảng Chi tiết phiếu nhập kho (chi_tiet_phieu_nhap) nếu chưa tồn tại
        if (! Schema::hasTable('chi_tiet_phieu_nhap')) {
            Schema::create('chi_tiet_phieu_nhap', function (Blueprint $table) {
                $table->id();
                $table->foreignId('id_phieu_nhap')->constrained('phieu_nhap_kho')->onDelete('cascade');
                $table->foreignId('id_sach')->constrained('sach')->onDelete('cascade');
                $table->integer('so_luong')->unsigned();
                $table->decimal('don_gia_nhap', 12, 0);
                $table->decimal('thanh_tien', 14, 0);
            });
        }

        // 4. Tạo bảng Đánh giá sách (danh_gia) nếu chưa tồn tại
        if (! Schema::hasTable('danh_gia')) {
            Schema::create('danh_gia', function (Blueprint $table) {
                $table->id();
                $table->foreignId('id_nguoi_dung')->constrained('nguoi_dung')->onDelete('cascade');
                $table->foreignId('id_sach')->constrained('sach')->onDelete('cascade');
                $table->unsignedTinyInteger('so_sao');
                $table->text('binh_luan')->nullable();
                $table->boolean('da_duyet')->default(true);
                $table->timestamp('ngay_tao')->useCurrent();
                $table->timestamp('ngay_cap_nhat')->useCurrent()->useCurrentOnUpdate();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('danh_gia');
        Schema::dropIfExists('chi_tiet_phieu_nhap');
        Schema::dropIfExists('phieu_nhap_kho');

        if (Schema::hasTable('kho_hang')) {
            Schema::table('kho_hang', function (Blueprint $table) {
                $table->dropColumn(['khu_vuc', 'ke_hang', 'o_chua']);
            });
        }
    }
};
