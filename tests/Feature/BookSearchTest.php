<?php

namespace Tests\Feature;

use App\Models\NhaXuatBan;
use App\Models\Sach;
use App\Models\TacGia;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_matches_author_publisher_and_isbn(): void
    {
        $publisher = NhaXuatBan::create(['ten_nxb' => 'Nhà xuất bản Ánh Dương']);
        $author = TacGia::create(['ten_tac_gia' => 'Nguyễn Nhật Ánh']);
        $book = Sach::create([
            'tieu_de' => 'Tuyển tập truyện ngắn',
            'duong_dan_tinh' => 'tuyen-tap-truyen-ngan-search',
            'id_nha_xuat_ban' => $publisher->id,
            'gia_ban' => 90000,
            'ma_isbn' => '9786040000123',
            'dang_hoat_dong' => true,
        ]);
        $book->tacGia()->attach($author->id);

        $this->get(route('books.search', ['keyword' => 'Nguyễn Nhật']))
            ->assertOk()
            ->assertSee($book->tieu_de)
            ->assertSee($author->ten_tac_gia);

        $this->get(route('books.search', ['keyword' => 'Ánh Dương']))
            ->assertOk()
            ->assertSee($book->tieu_de);

        $this->get(route('books.search', ['keyword' => '9786040000123']))
            ->assertOk()
            ->assertSee($book->tieu_de);
    }

    public function test_search_matches_full_text_in_book_description(): void
    {
        $book = Sach::create([
            'tieu_de' => 'Tuyển tập truyện',
            'duong_dan_tinh' => 'tuyen-tap-truyen-full-text-search',
            'gia_ban' => 90000,
            'mo_ta' => 'Hành trình khám phá khu rừng bí mật',
            'dang_hoat_dong' => true,
        ]);

        $this->get(route('books.search', ['keyword' => 'khu rừng bí mật']))
            ->assertOk()
            ->assertSee($book->tieu_de);
    }

    public function test_suggestions_return_matching_active_books_with_author_and_isbn(): void
    {
        $publisher = NhaXuatBan::create(['ten_nxb' => 'Nhà xuất bản Sao Mai']);
        $author = TacGia::create(['ten_tac_gia' => 'Linh Tran']);
        $book = Sach::create([
            'tieu_de' => 'Sách tìm kiếm gợi ý',
            'duong_dan_tinh' => 'sach-tim-kiem-goi-y',
            'id_nha_xuat_ban' => $publisher->id,
            'gia_ban' => 90000,
            'ma_isbn' => '9786040000456',
            'dang_hoat_dong' => true,
        ]);
        $book->tacGia()->attach($author->id);
        Sach::create([
            'tieu_de' => 'Sách tìm kiếm đã ẩn',
            'duong_dan_tinh' => 'sach-tim-kiem-da-an',
            'gia_ban' => 90000,
            'dang_hoat_dong' => false,
        ]);

        $this->getJson(route('books.search.suggestions', ['keyword' => 'Sách tìm']))
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.title', $book->tieu_de)
            ->assertJsonPath('0.authors', $author->ten_tac_gia)
            ->assertJsonPath('0.publisher', $publisher->ten_nxb)
            ->assertJsonPath('0.isbn', $book->ma_isbn)
            ->assertJsonPath('0.url', route('books.show', $book->id));
    }

    public function test_suggestions_wait_for_at_least_two_characters(): void
    {
        $this->getJson(route('books.search.suggestions', ['keyword' => 'a']))
            ->assertOk()
            ->assertExactJson([]);
    }

    public function test_search_without_a_keyword_returns_to_the_catalog(): void
    {
        $this->get(route('books.search'))
            ->assertRedirect(route('books.index'));
    }
}
