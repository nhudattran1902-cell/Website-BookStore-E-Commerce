<?php

namespace Tests\Feature;

use App\Models\ChiTietDonHang;
use App\Models\DanhGiaSach;
use App\Models\DonHang;
use App\Models\NguoiDung;
use App\Models\Sach;
use App\Models\TheLoai;
use App\Models\TrangSach;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_external_seeded_cover_urls_are_not_prefixed_with_storage_path(): void
    {
        $externalCoverUrl = 'https://images.unsplash.com/photo-1532012197267-da84d127e765?w=500';
        $book = Sach::create([
            'tieu_de' => 'Sách có ảnh bìa bên ngoài',
            'duong_dan_tinh' => 'sach-co-anh-bia-ben-ngoai',
            'gia_ban' => 50000,
            'anh_bia' => $externalCoverUrl,
            'dang_hoat_dong' => true,
        ]);
        $previewPageUrl = 'https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe?w=800';
        TrangSach::create([
            'id_sach' => $book->id,
            'so_trang' => 1,
            'duong_dan_anh' => $previewPageUrl,
            'cho_phep_doc_thu' => true,
        ]);

        $this->get(route('books.show', $book->id))
            ->assertOk()
            ->assertSee('src="'.$externalCoverUrl.'"', false)
            ->assertSee('src="'.$previewPageUrl.'"', false)
            ->assertDontSee('storage/'.$externalCoverUrl, false);
    }

    /**
     * Test 1: Khách hàng thấy thông tin sách và CHỈ thấy các đánh giá ĐÃ DUYỆT (da_duyet = true)
     */
    public function test_customer_can_view_book_details_and_only_approved_reviews(): void
    {
        // 1. Khởi tạo dữ liệu mẫu
        $user = NguoiDung::factory()->create();
        $category = TheLoai::create([
            'ten_the_loai' => 'Văn Học',
            'duong_dan_tinh' => 'van-hoc',
            'mo_ta' => 'Thể loại văn học',
        ]);

        $book = Sach::create([
            'tieu_de' => 'Dế Mèn Phiêu Lưu Ký',
            'duong_dan_tinh' => 'de-men-phieu-luu-ky',
            'id_the_loai' => $category->id,
            'gia_ban' => 50000,
            'mo_ta' => 'Một tác phẩm kinh điển của Tô Hoài.',
            'dang_hoat_dong' => true,
        ]);

        // Đánh giá ĐÃ DUYỆT -> Phải hiển thị
        $approvedReview = DanhGiaSach::create([
            'id_nguoi_dung' => $user->id,
            'id_sach' => $book->id,
            'so_sao' => 5,
            'binh_luan' => 'Sách rất hay và giàu ý nghĩa!',
            'da_duyet' => true,
        ]);

        // Đánh giá CHỜ DUYỆT -> Không được hiển thị
        $pendingUser = NguoiDung::factory()->create();
        $pendingReview = DanhGiaSach::create([
            'id_nguoi_dung' => $pendingUser->id,
            'id_sach' => $book->id,
            'so_sao' => 1,
            'binh_luan' => 'Nội dung quảng cáo spam!',
            'da_duyet' => false,
        ]);

        // 2. Gọi URL chi tiết sách
        $response = $this->get(route('books.show', $book->id));

        // 3. Kiểm tra kết quả
        $response->assertStatus(200);
        $response->assertSee('Dế Mèn Phiêu Lưu Ký');
        $response->assertSee('Một tác phẩm kinh điển của Tô Hoài.');
        $response->assertSee('Sách rất hay và giàu ý nghĩa!');
        $response->assertDontSee('Nội dung quảng cáo spam!');
    }

    /**
     * Test 2: Khách CHƯA đăng nhập gửi đánh giá sẽ bị chuyên hướng về trang Đăng nhập (302)
     */
    public function test_unauthenticated_user_cannot_submit_review(): void
    {
        $book = Sach::create([
            'tieu_de' => 'Sách Test Login',
            'duong_dan_tinh' => 'sach-test-login',
            'gia_ban' => 100000,
            'dang_hoat_dong' => true,
        ]);

        $response = $this->post(route('books.reviews.store', $book->id), [
            'so_sao' => 5,
            'binh_luan' => 'Thử gửi khi chưa login',
        ]);

        // Yêu cầu chuyển hướng đến trang login
        $response->assertRedirect(route('login'));
        $this->assertDatabaseCount('danh_gia', 0);
    }

    /**
     * Test 3: Khách ĐÃ đăng nhập gửi đánh giá thành công và kiểm tra Validation số sao
     */
    public function test_authenticated_user_can_submit_valid_review(): void
    {
        $user = NguoiDung::factory()->create();
        $book = Sach::create([
            'tieu_de' => 'Sách Test Review',
            'duong_dan_tinh' => 'sach-test-review',
            'gia_ban' => 80000,
            'dang_hoat_dong' => true,
        ]);
        $this->createOrderWithBook($user, $book);
        // Đăng nhập tài khoản khách
        $this->actingAs($user);

        // Gửi đánh giá hợp lệ
        $response = $this->post(route('books.reviews.store', $book->id), [
            'so_sao' => 4,
            'binh_luan' => 'Giao hàng nhanh, đóng gói cẩn thận.',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('danh_gia', [
            'id_nguoi_dung' => $user->id,
            'id_sach' => $book->id,
            'so_sao' => 4,
            'binh_luan' => 'Giao hàng nhanh, đóng gói cẩn thận.',
            'da_duyet' => false,
        ]);
    }

    /**
     * Test 4: Danh sách sách liên quan KHÔNG CHỨA chính cuốn sách đang xem
     */
    public function test_related_books_does_not_contain_current_book(): void
    {
        $category = TheLoai::create([
            'ten_the_loai' => 'Kinh Tế',
            'duong_dan_tinh' => 'kinh-te',
        ]);

        $currentBook = Sach::create([
            'tieu_de' => 'Sách Đang Xem',
            'duong_dan_tinh' => 'sach-dang-xem',
            'id_the_loai' => $category->id,
            'gia_ban' => 120000,
            'dang_hoat_dong' => true,
        ]);

        $otherBook = Sach::create([
            'tieu_de' => 'Sách Cùng Thể Loại',
            'duong_dan_tinh' => 'sach-cung-the-loai',
            'id_the_loai' => $category->id,
            'gia_ban' => 150000,
            'dang_hoat_dong' => true,
        ]);

        $response = $this->get(route('books.show', $currentBook->id));

        $response->assertStatus(200);
        // Kiểm tra biến $relatedBooks được truyền sang View không chứa ID của $currentBook
        $relatedBooks = $response->viewData('relatedBooks');
        $this->assertFalse($relatedBooks->contains('id', $currentBook->id));
        $this->assertTrue($relatedBooks->contains('id', $otherBook->id));
    }

    /**
     * Test 5: Lịch sử sách đã xem trong Session không chứa ID trùng lặp
     */
    public function test_recently_viewed_books_session_does_not_contain_duplicates(): void
    {
        $book = Sach::create([
            'tieu_de' => 'Sách Lịch Sử Trùng',
            'duong_dan_tinh' => 'sach-lich-su-trung',
            'gia_ban' => 90000,
            'dang_hoat_dong' => true,
        ]);

        // Xem sách lần 1
        $this->get(route('books.show', $book->id));
        // Xem sách lần 2
        $this->get(route('books.show', $book->id));

        // Kiểm tra mảng Session vừa xem
        $recentlyViewed = session()->get('recently_viewed_books', []);

        // Đảm bảo ID sách chỉ xuất hiện đúng 1 lần trong mảng
        $this->assertEquals(1, array_count_values($recentlyViewed)[$book->id]);
    }

    public function test_catalog_groups_books_by_topic_series_and_numeric_volume(): void
    {
        $category = TheLoai::create([
            'ten_the_loai' => 'Manga - Truyện thiếu nhi',
            'duong_dan_tinh' => 'manga-truyen-thieu-nhi',
        ]);

        foreach ([10, 2, 1] as $volumeNumber) {
            $title = 'Doraemon - Tập '.$volumeNumber;
            Sach::create([
                'tieu_de' => $title,
                'duong_dan_tinh' => str()->slug($title),
                'id_the_loai' => $category->id,
                'gia_ban' => 25000,
                'dang_hoat_dong' => true,
            ]);
        }

        $standaloneTitle = 'Sách lẻ không thuộc bộ';
        Sach::create([
            'tieu_de' => $standaloneTitle,
            'duong_dan_tinh' => str()->slug($standaloneTitle),
            'id_the_loai' => $category->id,
            'gia_ban' => 30000,
            'dang_hoat_dong' => true,
        ]);

        $this->get(route('books.catalog'))
            ->assertOk()
            ->assertSeeInOrder([
                'Manga - Truyện thiếu nhi',
                'Doraemon',
                'Tập 1',
                'Tập 2',
                'Tập 10',
                'Sách lẻ',
                'Sách lẻ không thuộc bộ',
            ])
            ->assertSee(route('books.index'));
    }

    public function test_book_path_alias_displays_the_catalog(): void
    {
        $this->get('/book')->assertOk()->assertSee('Chủ đề và bộ truyện');
    }

    private function createOrderWithBook(
        NguoiDung $customer,
        Sach $book,
        string $status = 'hoan_thanh',
    ): DonHang {
        $order = DonHang::create([
            'ma_don_hang' => 'ORD-REVIEW-'.$customer->id.'-'.$book->id,
            'id_nguoi_dung' => $customer->id,
            'tong_tien' => 80000,
            'so_tien_giam_gia' => 0,
            'thanh_tien' => 80000,
            'trang_thai' => $status,
        ]);

        ChiTietDonHang::create([
            'id_don_hang' => $order->id,
            'id_sach' => $book->id,
            'don_gia' => 80000,
            'so_luong' => 1,
            'thanh_tien' => 80000,
        ]);

        return $order;
    }

    public function test_customer_cannot_review_book_without_completed_purchase(): void
    {
        $user = NguoiDung::factory()->create();
        $book = Sach::create([
            'tieu_de' => 'Sách chưa mua',
            'duong_dan_tinh' => 'sach-chua-mua',
            'gia_ban' => 80000,
            'dang_hoat_dong' => true,
        ]);

        $response = $this->actingAs($user)->post(route('books.reviews.store', $book->id), [
            'so_sao' => 5,
            'binh_luan' => 'Đánh giá thử.',
        ]);

        $response->assertSessionHasErrors('review');
        $this->assertDatabaseCount('danh_gia', 0);
    }

    public function test_customer_cannot_review_before_order_is_completed(): void
    {
        $user = NguoiDung::factory()->create();
        $book = Sach::create([
            'tieu_de' => 'Sách đang giao',
            'duong_dan_tinh' => 'sach-dang-giao',
            'gia_ban' => 80000,
            'dang_hoat_dong' => true,
        ]);
        $this->createOrderWithBook($user, $book, 'dang_giao');

        $response = $this->actingAs($user)->post(route('books.reviews.store', $book->id), [
            'so_sao' => 5,
            'binh_luan' => 'Đánh giá khi đơn vẫn đang giao.',
        ]);

        $response->assertSessionHasErrors('review');
        $this->assertDatabaseCount('danh_gia', 0);
    }
}
