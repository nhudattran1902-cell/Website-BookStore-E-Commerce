<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Chạy toàn bộ migration nghiệp vụ cho website bán sách BOOK & BOX.
     * Thứ tự tạo bảng tuân thủ ràng buộc khóa ngoại (FK).
     */
    public function up(): void
    {
        // 1. Bảng vai_tro
        Schema::create('vai_tro', function (Blueprint $table) {
            $table->id();
            $table->string('ten_vai_tro', 50)->unique();
            $table->string('mo_ta', 255)->nullable();
            $table->timestamp('ngay_tao')->useCurrent();
            $table->timestamp('ngay_cap_nhat')->useCurrent()->useCurrentOnUpdate();
        });

        // 2. Bảng nguoi_dung
        Schema::create('nguoi_dung', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->unique()->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('ho_ten', 150);

            $table->string('so_dien_thoai', 20)->nullable();
            $table->string('anh_dai_dien', 500)->nullable();
            $table->string('dia_chi_mac_dinh', 500)->nullable();
            $table->timestamp('ngay_tao')->useCurrent();
            $table->timestamp('ngay_cap_nhat')->useCurrent()->useCurrentOnUpdate();
        });

        // 3. Bảng pivot vai_tro_nguoi_dung
        Schema::create('vai_tro_nguoi_dung', function (Blueprint $table) {
            $table->unsignedBigInteger('id_nguoi_dung');
            $table->unsignedBigInteger('id_vai_tro');
            $table->primary(['id_nguoi_dung', 'id_vai_tro']);
            $table->foreign('id_nguoi_dung')->references('id')->on('nguoi_dung')->onDelete('cascade');
            $table->foreign('id_vai_tro')->references('id')->on('vai_tro')->onDelete('cascade');
        });

        // 4. Bảng the_loai
        Schema::create('the_loai', function (Blueprint $table) {
            $table->id();
            $table->string('ten_the_loai', 150)->unique();
            $table->string('duong_dan_tinh', 200)->unique();
            $table->text('mo_ta')->nullable();
            $table->timestamp('ngay_tao')->useCurrent();
            $table->timestamp('ngay_cap_nhat')->useCurrent()->useCurrentOnUpdate();
        });

        // 5. Bảng tac_gia
        Schema::create('tac_gia', function (Blueprint $table) {
            $table->id();
            $table->string('ten_tac_gia', 150);
            $table->string('quoc_tich', 100)->nullable();
            $table->text('tieu_su')->nullable();
            $table->string('anh_dai_dien', 500)->nullable();
            $table->timestamp('ngay_tao')->useCurrent();
            $table->timestamp('ngay_cap_nhat')->useCurrent()->useCurrentOnUpdate();
        });

        // 6. Bảng nha_xuat_ban (Đã bổ sung 'email' để khớp với Seeder)
        Schema::create('nha_xuat_ban', function (Blueprint $table) {
            $table->id();
            $table->string('ten_nxb', 200);
            $table->string('dia_chi', 500)->nullable();
            $table->string('website', 255)->nullable();
            $table->string('email', 150)->nullable(); // Thêm để đồng bộ với AllTableSeeder[cite: 1]
            $table->string('logo', 500)->nullable();
            $table->timestamp('ngay_tao')->useCurrent();
            $table->timestamp('ngay_cap_nhat')->useCurrent()->useCurrentOnUpdate();
        });

        // 7. Bảng sach (Đổi 'isbn' thành 'ma_isbn' để khớp với Form/Controller/View)[cite: 1]
        Schema::create('sach', function (Blueprint $table) {
            $table->id();
            $table->string('tieu_de', 300);
            $table->string('duong_dan_tinh', 350)->unique();
            $table->unsignedBigInteger('id_the_loai')->nullable();
            $table->unsignedBigInteger('id_nha_xuat_ban')->nullable();
            $table->decimal('gia_ban', 12, 0);
            $table->decimal('gia_khuyen_mai', 12, 0)->nullable();
            $table->string('anh_bia', 500)->nullable();
            $table->text('mo_ta')->nullable();
            $table->year('nam_xuat_ban')->nullable();
            $table->integer('so_trang')->unsigned()->nullable();
            $table->string('ma_isbn', 50)->nullable()->unique(); // Đã sửa thành ma_isbn[cite: 1]
            $table->boolean('noi_bat')->default(false);
            $table->boolean('ban_chay')->default(false);
            $table->boolean('dang_hoat_dong')->default(true);
            $table->foreign('id_the_loai')->references('id')->on('the_loai')->onDelete('set null');
            $table->foreign('id_nha_xuat_ban')->references('id')->on('nha_xuat_ban')->onDelete('set null');
            $table->timestamp('ngay_tao')->useCurrent();
            $table->timestamp('ngay_cap_nhat')->useCurrent()->useCurrentOnUpdate();
        });

        // 8. Bảng pivot sach_tac_gia
        Schema::create('sach_tac_gia', function (Blueprint $table) {
            $table->unsignedBigInteger('id_sach');
            $table->unsignedBigInteger('id_tac_gia');
            $table->primary(['id_sach', 'id_tac_gia']);
            $table->foreign('id_sach')->references('id')->on('sach')->onDelete('cascade');
            $table->foreign('id_tac_gia')->references('id')->on('tac_gia')->onDelete('cascade');
        });

        // 9. Bảng kho_hang (Đã bổ sung cột so_luong_dat_truoc)[cite: 1]
        Schema::create('kho_hang', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_sach')->unique();
            $table->integer('so_luong_ton')->unsigned()->default(0);
            $table->integer('so_luong_dat_truoc')->unsigned()->default(0); // Thêm cột này[cite: 1]
            $table->integer('nguong_canh_bao')->unsigned()->default(5);
            $table->foreign('id_sach')->references('id')->on('sach')->onDelete('cascade');
            $table->timestamp('ngay_tao')->useCurrent();
            $table->timestamp('ngay_cap_nhat')->useCurrent()->useCurrentOnUpdate();
        });

        // 10. Bảng trang_sach
        Schema::create('trang_sach', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_sach');
            $table->integer('so_trang')->unsigned();
            $table->string('duong_dan_anh', 500);
            $table->boolean('cho_phep_doc_thu')->default(false);
            $table->foreign('id_sach')->references('id')->on('sach')->onDelete('cascade');
            $table->timestamp('ngay_tao')->useCurrent();
            $table->timestamp('ngay_cap_nhat')->useCurrent()->useCurrentOnUpdate();
        });

        // 11. Bảng hinh_anh_sach
        Schema::create('hinh_anh_sach', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_sach');
            $table->string('duong_dan_anh', 500);
            $table->string('chu_thich', 255)->nullable();
            $table->integer('thu_tu')->unsigned()->default(0);
            $table->foreign('id_sach')->references('id')->on('sach')->onDelete('cascade');
            $table->timestamp('ngay_tao')->useCurrent();
            $table->timestamp('ngay_cap_nhat')->useCurrent()->useCurrentOnUpdate();
        });

        // 12. Bảng gio_hang
        Schema::create('gio_hang', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_nguoi_dung')->unique();
            $table->foreign('id_nguoi_dung')->references('id')->on('nguoi_dung')->onDelete('cascade');
            $table->timestamp('ngay_tao')->useCurrent();
            $table->timestamp('ngay_cap_nhat')->useCurrent()->useCurrentOnUpdate();
        });

        // 13. Bảng chi_tiet_gio_hang
        Schema::create('chi_tiet_gio_hang', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_gio_hang');
            $table->unsignedBigInteger('id_sach');
            $table->integer('so_luong')->unsigned()->default(1);
            $table->unique(['id_gio_hang', 'id_sach']);
            $table->foreign('id_gio_hang')->references('id')->on('gio_hang')->onDelete('cascade');
            $table->foreign('id_sach')->references('id')->on('sach')->onDelete('cascade');
            $table->timestamp('ngay_tao')->useCurrent();
            $table->timestamp('ngay_cap_nhat')->useCurrent()->useCurrentOnUpdate();
        });
        // 14. Bảng ma_giam_gia (thêm trước don_hang)
        Schema::create('ma_giam_gia', function (Blueprint $table) {
    $table->id();
    $table->string('ma_code', 50)->unique();
    $table->enum('loai_giam', ['phan_tram', 'so_tien_co_dinh']);
    $table->decimal('gia_tri', 12, 0);                      // % hoặc VNĐ
    $table->decimal('gia_tri_toi_da', 12, 0)->nullable();   // giới hạn giảm tối đa (dùng khi loại %)
    $table->decimal('don_toi_thieu', 14, 0)->default(0);    // đơn hàng tối thiểu để áp dụng
    $table->integer('gioi_han_luot')->unsigned()->nullable(); // null = không giới hạn
    $table->integer('da_su_dung')->unsigned()->default(0);
    $table->timestamp('ngay_bat_dau')->nullable();
    $table->timestamp('ngay_het_han')->nullable();
    $table->boolean('dang_hoat_dong')->default(true);
    $table->timestamp('ngay_ta  o')->useCurrent();
    $table->timestamp('ngay_cap_nhat')->useCurrent()->useCurrentOnUpdate();
});/*  */
        // 14. Bảng don_hang
        Schema::create('don_hang', function (Blueprint $table) {
            $table->id();
            $table->string('ma_don_hang', 30)->unique();
            $table->unsignedBigInteger('id_nguoi_dung');
            $table->decimal('tong_tien', 14, 0);
            $table->decimal('so_tien_giam_gia', 14, 0)->default(0);
            $table->decimal('thanh_tien', 14, 0);
            $table->enum('trang_thai', ['cho_xu_ly', 'dang_xu_ly', 'dang_giao', 'hoan_thanh', 'da_huy'])
                ->default('cho_xu_ly');
            $table->text('dia_chi_giao_hang');
            $table->text('ghi_chu')->nullable();
            $table->unsignedBigInteger('id_nguoi_dung');
$table->unsignedBigInteger('id_ma_giam_gia')->nullable(); // ← VẪN THIẾU
$table->decimal('tong_tien', 14, 0);

$table->foreign('id_nguoi_dung')->references('id')->on('nguoi_dung')->onDelete('cascade');
$table->foreign('id_ma_giam_gia')->references('id')->on('ma_giam_gia')->onDelete('set null'); // ← VẪN THIẾU
            $table->timestamp('ngay_tao')->useCurrent();
            $table->timestamp('ngay_cap_nhat')->useCurrent()->useCurrentOnUpdate();
        });

        // 15. Bảng chi_tiet_don_hang
        Schema::create('chi_tiet_don_hang', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_don_hang');
            $table->unsignedBigInteger('id_sach');
            $table->decimal('don_gia', 12, 0);
            $table->integer('so_luong')->unsigned();
            $table->decimal('thanh_tien', 14, 0);
            $table->foreign('id_don_hang')->references('id')->on('don_hang')->onDelete('cascade');
            $table->foreign('id_sach')->references('id')->on('sach')->onDelete('restrict');
            $table->timestamp('ngay_tao')->useCurrent();
            $table->timestamp('ngay_cap_nhat')->useCurrent()->useCurrentOnUpdate();
        });

        // 16. Bảng thanh_toan
        Schema::create('thanh_toan', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_don_hang')->unique();
            $table->enum('phuong_thuc_thanh_toan', ['COD', 'MoMo', 'VNPay'])->default('COD');
            $table->decimal('so_tien', 14, 0);
            $table->enum('trang_thai', ['cho_thanh_toan', 'da_thanh_toan', 'hoan_tien'])->default('cho_thanh_toan');
            $table->string('ma_giao_dich', 100)->nullable();
            $table->timestamp('ngay_thanh_toan')->nullable();
            $table->foreign('id_don_hang')->references('id')->on('don_hang')->onDelete('cascade');
            $table->timestamp('ngay_tao')->useCurrent();
            $table->timestamp('ngay_cap_nhat')->useCurrent()->useCurrentOnUpdate();
        });
    }

    /**
     * Rollback — xóa ngược thứ tự để không vi phạm FK.
     */
    public function down(): void
    {
        Schema::dropIfExists('thanh_toan');
        Schema::dropIfExists('chi_tiet_don_hang');
        Schema::dropIfExists('don_hang');
        Schema::dropIfExists('ma_giam_gia');
        Schema::dropIfExists('chi_tiet_gio_hang');
        Schema::dropIfExists('gio_hang');
        Schema::dropIfExists('hinh_anh_sach');
        Schema::dropIfExists('trang_sach');
        Schema::dropIfExists('kho_hang');
        Schema::dropIfExists('sach_tac_gia');
        Schema::dropIfExists('sach');
        Schema::dropIfExists('nha_xuat_ban');
        Schema::dropIfExists('tac_gia');
        Schema::dropIfExists('the_loai');
        Schema::dropIfExists('vai_tro_nguoi_dung');
        Schema::dropIfExists('nguoi_dung');
        Schema::dropIfExists('vai_tro');
    }
};
 -->