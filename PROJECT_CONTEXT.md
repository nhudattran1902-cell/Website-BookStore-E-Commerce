# BOOK & BOX: Ngữ cảnh hệ thống

> Tài liệu bàn giao cho AI tiếp quản và phát triển dự án. Nội dung phản ánh workspace được kiểm tra ngày 2026-09-28. Trước khi sửa, hãy xác minh lại source và phiên bản package đang cài.

## 1. Tổng quan dự án

BOOK & BOX là website bán sách tiếng Việt, gồm cửa hàng trực tuyến và khu vực quản trị. Chức năng chính: duyệt/tìm sách, tài khoản, giỏ hàng, thanh toán, lịch sử đơn hàng và quản lý sách, danh mục, kho, đơn hàng, người dùng.

| Hạng mục | Công nghệ |
| --- | --- |
| Backend | PHP 8.3; Laravel Framework 13.29.0 |
| Cơ sở dữ liệu | MySQL, cấu hình qua Laravel và biến môi trường |
| View | Blade; Bootstrap 5.3 ở storefront, Bootstrap local ở admin |
| Frontend build | Vite 8, Laravel Vite Plugin 3.1, Tailwind CSS 4; đồng thời có CSS tùy chỉnh theo Bootstrap |
| Kiểm thử / AI tooling | PHPUnit 12.5.33; Laravel Boost 2.10.0 |

Phiên bản Composer và npm được khai báo trong `composer.json` và `package.json`; phiên bản package PHP thực tế nêu trên được xác nhận từ `composer.lock`.

## 2. Kiến trúc và luồng dữ liệu

### Vai trò thư mục

- `routes/web.php`: khai báo route công khai, guest, khách hàng đã đăng nhập và admin.
- `app/Http/Controllers`: xác thực request, điều phối nghiệp vụ, truy vấn và chọn response; chia namespace `Admin` và `Customer`.
- `app/Http/Middleware/AdminMiddleware.php`: kiểm tra đăng nhập và role `admin`; alias được đăng ký trong `bootstrap/app.php`.
- `app/Models`: Eloquent models cho tài khoản, sách, đơn hàng, giỏ hàng, kho, vai trò và thực thể liên quan. Nhiều model dùng timestamp `ngay_tao` / `ngay_cap_nhat`.
- `database/migrations`: schema framework và nghiệp vụ; `database/seeders`: dữ liệu mẫu.
- `resources/views`: Blade templates cho storefront, admin, khách hàng, xác thực và trang tĩnh.
- `resources/css/app.css`, `resources/js/app.js`: style storefront và xử lý ảnh lazy-load. Asset admin nằm trong `public/admin_assets`.
- `config`: cấu hình auth, database, session, cache, filesystem, queue và các thành phần Laravel khác.
- `tests`: PHPUnit; các test hiện tại là ví dụ scaffold, chưa bao phủ nghiệp vụ.

### Request lifecycle

Request từ trình duyệt -> route trong `routes/web.php` -> middleware (`guest` / `auth` / `admin`) -> controller -> Eloquent hoặc Query Builder -> Blade view -> layout và asset dùng chung.

Ứng dụng chủ yếu render phía server. Trong các route đã kiểm tra, chưa thấy API layer riêng.

### Các luồng nghiệp vụ chính

1. Storefront: `/` tải danh mục, sách bán chạy/nổi bật và tác giả qua `HomeController`; `/books` lọc, sắp xếp, phân trang; `/books/{id}` tải chi tiết sách, tác giả, trang đọc thử; `/search` tìm sách đang hoạt động theo tên hoặc thể loại.
2. Xác thực: `AuthController` đọc `nguoi_dung`, kiểm tra `mat_khau` bằng Laravel Hash, đăng nhập qua web guard; admin được chuyển tới `/admin/dashboard`, khách hàng về `/`.
3. Giỏ hàng: khách đã đăng nhập thêm/cập nhật `chi_tiet_gio_hang`; thao tác sửa/xóa kiểm tra quyền sở hữu. Tổng tiền ưu tiên giá khuyến mãi nếu có, nếu không dùng giá bán.
4. Checkout: kiểm tra tồn kho, tạo đơn và chi tiết, trừ kho, tạo thanh toán, xóa giỏ hàng rồi chuyển đến trang thành công.
5. Admin: `/admin/*` yêu cầu `auth` và `admin`; controller thao tác model nghiệp vụ và trả về view `admin.*`.

## 3. Tóm tắt file cốt lõi

### Routing và cấu hình ứng dụng

| File | Chức năng / symbol chính | Liên kết |
| --- | --- | --- |
| `routes/web.php` | Khai báo storefront, auth, cart, checkout, profile/lịch sử đơn và admin; dùng named routes như `books.index`, `cart.add`, `admin.orders.index`. | Điều phối request đến các controller bên dưới. |
| `bootstrap/app.php` | Cấu hình web/console routes, health endpoint `/up`, exception behavior và alias middleware `admin`. | `routes/web.php`, `AdminMiddleware`. |
| `app/Http/Middleware/AdminMiddleware.php` | `handle()` chuyển user chưa đăng nhập về login và trả 403 nếu `hasRole('admin')` sai. | `NguoiDung::hasRole()`, nhóm admin routes. |
| `config/auth.php` | Web guard dùng Eloquent provider với model `App\Models\NguoiDung`. | `AuthController`, `NguoiDung`. |

### Controllers

| File | Hàm quan trọng và nhiệm vụ | Liên kết chính |
| --- | --- | --- |
| `app/Http/Controllers/HomeController.php` | `index()` truy vấn danh mục, các bộ sách và tác giả cho trang chủ. | `home.index`; bảng `the_loai`, `sach`, `sach_tac_gia`, `tac_gia`. |
| `app/Http/Controllers/BookController.php` | `index()` lọc/sắp xếp/phân trang; `show()` tải sách, tác giả, trang đọc thử; `search()` tìm theo tiêu đề/thể loại. | View `books.*`, `Sach`, các bảng catalog. |
| `app/Http/Controllers/AuthController.php` | `showLogin()`, `login()`, `showRegister()`, `register()`, `logout()` xử lý session auth và đăng ký. | `NguoiDung`, quan hệ role `VaiTro`, auth views. |
| `app/Http/Controllers/Customer/CartController.php` | `index()`, `addToCart()`, `updateCart()`, `destroy()` quản lý giỏ của user và kiểm tra quyền sở hữu khi sửa. | `GioHang`, `ChiTietGioHang`, `Sach`, cart view. |
| `app/Http/Controllers/Customer/CheckoutController.php` | `index()` tạo tóm tắt checkout; `process()` validate, kiểm kho, ghi đơn/thanh toán trong transaction và xóa giỏ; `success()` kiểm tra chủ sở hữu đơn. | `DonHang`, `ChiTietDonHang`, `KhoHang`, `ThanhToan`, checkout views. |
| `app/Http/Controllers/Customer/OrderController.php` | `index()` phân trang đơn của user hiện tại; `show()` giới hạn xem chi tiết theo user. | `DonHang`, customer order views. |
| `app/Http/Controllers/Customer/ProfileController.php` | `index()`, `update()`, `changePassword()` hiển thị/cập nhật hồ sơ và mật khẩu. | `Auth`, `Hash`, profile view. |
| `app/Http/Controllers/Admin/DashboardController.php` | `index()` tính doanh thu đơn hoàn tất, số đơn chờ, tồn kho, đơn gần đây, số sách và user. | `DonHang`, `KhoHang`, `Sach`, `NguoiDung`, dashboard view. |
| `app/Http/Controllers/Admin/AdminBookController.php` | Resource actions `index/create/store/edit/update/destroy` quản lý sách và upload ảnh bìa. | `Sach`, `TheLoai`, `NhaXuatBan`, public storage, admin book views. |
| `app/Http/Controllers/Admin/CategoryController.php` | `index/store/update/destroy` quản lý thể loại và tạo slug. | `TheLoai`, admin category view. |
| `app/Http/Controllers/Admin/AuthorController.php` | `index/store/update/destroy` quản lý tác giả. | `TacGia`, admin author view. |
| `app/Http/Controllers/Admin/PublisherController.php` | `index/store/update/destroy` quản lý nhà xuất bản. | `NhaXuatBan`, admin publisher view. |
| `app/Http/Controllers/Admin/InventoryController.php` | `index()` phân trang tồn kho và tìm sách chưa có bản ghi kho; `update()` chỉnh số lượng/ngưỡng cảnh báo. | `KhoHang`, `Sach`, admin inventory view. |
| `app/Http/Controllers/Admin/BookPageController.php` | `index/store/update/destroy` quản lý ảnh trang đọc thử, hỗ trợ upload nhiều trang và xóa file cũ. | `TrangSach`, `Sach`, `Storage`, book-page routes/views. |
| `app/Http/Controllers/Admin/OrderController.php` | `index()` lọc/phân trang; `show()` tải chi tiết; `updateStatus()` validate và đổi trạng thái đơn. | `DonHang`, admin order views. |
| `app/Http/Controllers/Admin/UserController.php` | `index()` phân trang người dùng để admin xem. | `NguoiDung`, admin users view. |
| `app/Http/Controllers/PageController.php` | Trả về các trang giới thiệu, liên hệ, chính sách và FAQ. | `pages.*` views. |

### Models và persistence

| File(s) | Vai trò / quan hệ đáng chú ý |
| --- | --- |
| `app/Models/NguoiDung.php`, `VaiTro.php` | User xác thực tùy chỉnh và quan hệ role qua `vai_tro_nguoi_dung`; `NguoiDung::hasRole()` được dùng để phân quyền admin. |
| `app/Models/Sach.php`, `TheLoai.php`, `TacGia.php`, `NhaXuatBan.php`, `TrangSach.php`, `HinhAnhSach.php` | Catalog, tác giả/NXB, ảnh sách và trang đọc thử. `Sach` liên kết thể loại, NXB, kho và trang. Đối chiếu quan hệ tác giả với pivot trước khi sửa. |
| `app/Models/GioHang.php`, `ChiTietGioHang.php` | Giỏ hàng user và các dòng sách; schema dự kiến duy nhất một giỏ mỗi user, một dòng mỗi sách trong giỏ. |
| `app/Models/DonHang.php`, `ChiTietDonHang.php`, `ThanhToan.php` | Đơn hàng, dòng chi tiết và một bản ghi thanh toán mỗi đơn; timestamp đơn dùng tên cột tiếng Việt. |
| `app/Models/KhoHang.php` | Tồn kho theo sách, số lượng đặt trước và ngưỡng cảnh báo. |
| `database/migrations/2026_09_21_000001_create_business_tables.php` | Schema nghiệp vụ chính: role/user, catalog, kho, cart, coupon, order, payment. Đọc kỹ trước khi đổi DB; có lỗi được ghi ở phần dưới. |
| `database/seeders/DatabaseSeeder.php`, `AllTableSeeder.php` | Entry point seed và dữ liệu mẫu idempotent bằng `firstOrCreate`, `updateOrInsert`, `updateOrCreate`. |

### Views và frontend assets

| File(s) | Chức năng |
| --- | --- |
| `resources/views/layouts/app.blade.php` | Layout storefront: header, content, footer, promo trang chủ, chat widget, Bootstrap CDN và Vite assets. |
| `resources/views/admin/layouts/master.blade.php` | Layout admin có sidebar/header/footer và Bootstrap/admin assets local. |
| `resources/views/home/*`, `resources/views/books/*` | Trang chủ, danh sách/chi tiết/tìm kiếm sách và các section storefront. |
| `resources/views/auth/*`, `resources/views/customer/*` | Login/register, giỏ, checkout, lịch sử/chi tiết đơn và hồ sơ. |
| `resources/views/admin/*` | Dashboard và màn hình quản lý catalog/đơn/kho/user. |
| `resources/css/app.css` | Style storefront, responsive navbar, book card, image wrapper và loading animation. |
| `resources/js/app.js` | Hiện ảnh lazy sau khi tải; thay nội dung ảnh lỗi bằng fallback. |

## 4. Trạng thái hiện tại, logic phức tạp và rủi ro

### Logic đang có cần bảo toàn

- Checkout là luồng nhạy cảm với transaction: tính giá từ dữ liệu sách hiện tại, tạo order lines, khóa bản ghi kho khi trừ tồn, tạo payment trạng thái chờ và xóa các dòng giỏ.
- Phân quyền role là logic riêng của dự án; không thay `NguoiDung::hasRole()` hoặc middleware `admin` nếu chưa tính đến pivot tùy chỉnh và guard.
- Ảnh bìa và ảnh trang đọc thử lưu trên disk `public`; luồng cập nhật/xóa sẽ dọn file đã thay thế.
- Seeder chứa dữ liệu/tài khoản mẫu. Không coi đó là thông tin production hoặc đưa bí mật `.env` vào tài liệu/log.

### Phần dở dang hoặc chưa xác minh

- Các file sau đang rỗng trong workspace đã kiểm tra: `app/Http/Controllers/Admin/InventoryImportController.php`, `TransactionController.php`, `app/Http/Controllers/Customer/ReviewController.php`, `app/Http/Controllers/Admin/AdminReviewController.php`, `app/Models/PhieuNhapKho.php`, `ChiTietPhieuNhap.php`, `DanhGiaSach.php`, `TinNhanChat.php`, `resources/views/components/chat-widget.blade.php`, cùng migrations `2026_09_28_000001_create_import_receipts_and_reviews_tables.php` và `2026_09_28_000002_create_chat_messages_table.php`. Không thấy route đang hoạt động cho đánh giá, phiếu nhập kho hoặc chat trong `routes/web.php`.
- Checkout có lựa chọn COD, MoMo, VNPay nhưng luồng đã kiểm tra chỉ tạo payment record cục bộ ở trạng thái chờ; chưa thấy tích hợp cổng thanh toán.
- Migration có bảng `ma_giam_gia`, nhưng chưa thấy model/luồng validate và áp dụng coupon hoàn chỉnh trong routes/controllers đã kiểm tra.
- `tests/Feature/ExampleTest.php` chỉ kiểm tra trang chủ trả thành công; `tests/Unit/ExampleTest.php` kiểm tra một giá trị true. Danh sách test hiện tại chưa có test chuyên biệt cho auth, inventory, checkout hoặc phân quyền admin.

### Cảnh báo schema và quan hệ

1. Migration chính tạo `nguoi_dung` nhưng thiếu `email` và `mat_khau`, trong khi `AllTableSeeder`, `AuthController`, `NguoiDung` cần các cột này. Bảng còn có FK nullable `user_id` sang bảng Laravel `users` riêng; cần xác định rõ thiết kế tài khoản trước khi sửa schema.
2. Trong closure tạo `don_hang`, `id_nguoi_dung` và `tong_tien` được khai báo hai lần. Tên timestamp coupon xuất hiện là `ngay_ta  o`; các cột/FK coupon cần đối chiếu với schema dự kiến. Không nên mặc định migration database mới sẽ chạy được chỉ từ source hiện tại.
3. Migration tạo pivot nhiều-nhiều `sach_tac_gia`, seeders và truy vấn storefront cũng dùng pivot, nhưng `Sach::tacGia()` khai báo `belongsTo`. Cần thống nhất model, controller và view với quan hệ dự định trước khi sửa chức năng tác giả.
4. `NhaXuatBan::saches()` khai báo kiểu trả về `HasMany` nhưng model được kiểm tra chưa import kiểu này. Xác minh/sửa declaration nếu method được dùng hoặc chỉnh sửa.
5. Migration `users` tạo bảng `sessions`; tuy nhiên cần xử lý lỗi migration nghiệp vụ trước khi tin cậy trạng thái migration tổng thể. Driver session/cache/queue có thể cấu hình theo môi trường; không suy ra cấu hình production từ local.

### Hướng dẫn cho AI tiếp quản

- Bắt đầu từ `AGENTS.md` và đọc đúng file hiện tại trước khi sửa; workspace có thể thay đổi so với snapshot này.
- Giữ named routes, định danh DB tiếng Việt, custom timestamps và convention Blade hiện có trừ khi yêu cầu cần thay đổi.
- Khi sửa database/relationship, kiểm tra migration cùng mọi consumer. Không chạy migration phá hủy dữ liệu nếu chưa được cho phép rõ ràng.
- Chạy PHPUnit hẹp nhất cho hành vi thay đổi; sau khi sửa PHP, chạy `vendor/bin/pint --dirty --format agent` theo `AGENTS.md`.
- Không kết luận tính năng hoàn chỉnh chỉ vì có model, tên controller, menu hoặc một phần schema; xác nhận route, view và luồng persistence có nối với nhau.