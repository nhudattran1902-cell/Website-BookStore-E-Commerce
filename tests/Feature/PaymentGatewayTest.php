<?php

namespace Tests\Feature;

use App\Models\ChiTietDonHang;
use App\Models\DonHang;
use App\Models\NguoiDung;
use App\Models\Sach;
use App\Models\VaiTro;
use App\Notifications\PaymentReceivedNotification;
use App\Services\MoMoService;
use App\Services\VietQRService;
use App\Services\VNPayService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PaymentGatewayTest extends TestCase
{
    use RefreshDatabase;

    public function test_vnpay_ipn_checks_signature_and_amount_and_is_idempotent(): void
    {
        Config::set('services.vnpay.hash_secret', 'vnpay-test-secret');
        Config::set('services.vnpay.tmn_code', 'BOOKBOX01');
        [$order, $book] = $this->createOnlineOrder('VNPay');
        $callback = $this->signedVnpayCallback($order, '5000000', 'TXN-100');

        $this->postJson(route('api.payments.vnpay.ipn'), $callback)
            ->assertOk()
            ->assertJsonPath('RspCode', '00');

        $this->assertSame('dang_xu_ly', $order->fresh()->trang_thai);
        $this->assertSame('da_thanh_toan', $order->thanhToan()->firstOrFail()->trang_thai);
        $this->assertSame(4, $book->khoHang()->firstOrFail()->fresh()->so_luong_ton);
        $this->assertSame(0, $book->khoHang()->firstOrFail()->fresh()->so_luong_dat_truoc);
        $this->assertDatabaseCount('lich_su_don_hang', 1);

        $this->postJson(route('api.payments.vnpay.ipn'), $callback)
            ->assertOk()
            ->assertJsonPath('RspCode', '00');

        $this->assertSame(4, $book->khoHang()->firstOrFail()->fresh()->so_luong_ton);
        $this->assertDatabaseCount('lich_su_don_hang', 1);
    }

    public function test_vnpay_rejects_invalid_signature_and_amount_without_changing_order(): void
    {
        Config::set('services.vnpay.hash_secret', 'vnpay-test-secret');
        Config::set('services.vnpay.tmn_code', 'BOOKBOX01');
        [$order, $book] = $this->createOnlineOrder('VNPay');
        $callback = $this->signedVnpayCallback($order, '4000000', 'TXN-101');

        $this->postJson(route('api.payments.vnpay.ipn'), $callback)
            ->assertOk()
            ->assertJsonPath('RspCode', '04');

        $callback['vnp_SecureHash'] = 'invalid-signature';
        $this->postJson(route('api.payments.vnpay.ipn'), $callback)
            ->assertOk()
            ->assertJsonPath('RspCode', '97');

        $this->assertSame('cho_xu_ly', $order->fresh()->trang_thai);
        $this->assertSame('cho_thanh_toan', $order->thanhToan()->firstOrFail()->trang_thai);
        $this->assertSame(5, $book->khoHang()->firstOrFail()->fresh()->so_luong_ton);
        $this->assertSame(1, $book->khoHang()->firstOrFail()->fresh()->so_luong_dat_truoc);
    }

    public function test_vnpay_payment_url_contains_a_valid_signature_and_minor_unit_amount(): void
    {
        Config::set('services.vnpay.hash_secret', 'vnpay-test-secret');
        Config::set('services.vnpay.tmn_code', 'BOOKBOX01');
        Config::set('services.vnpay.base_url', 'https://sandbox.example.test/pay');
        [$order] = $this->createOnlineOrder('VNPay');

        $url = VNPayService::createPaymentUrl($order->ma_don_hang, 50000, '127.0.0.1');
        parse_str((string) parse_url($url, PHP_URL_QUERY), $query);

        $this->assertSame('5000000', (string) $query['vnp_Amount']);
        $this->assertSame('Thanh toan don hang '.$order->ma_don_hang, $query['vnp_OrderInfo']);
        $this->assertTrue(VNPayService::verifyResponse($query));
    }

    public function test_momo_ipn_verifies_signature_and_confirms_payment(): void
    {
        Config::set('services.momo.access_key', 'momo-access');
        Config::set('services.momo.secret_key', 'momo-test-secret');
        Config::set('services.momo.partner_code', 'MOMO01');
        [$order, $book] = $this->createOnlineOrder('MoMo');
        $callback = [
            'amount' => '50000',
            'extraData' => '',
            'message' => 'Successful.',
            'orderId' => $order->ma_don_hang,
            'orderInfo' => 'Thanh toan don hang '.$order->ma_don_hang,
            'orderType' => 'momo_wallet',
            'partnerCode' => 'MOMO01',
            'payType' => 'qr',
            'requestId' => 'REQ-200',
            'responseTime' => '1790784000000',
            'resultCode' => '0',
            'transId' => 'MOMO-TXN-200',
        ];
        $callback['signature'] = $this->momoSignature($callback);

        $this->assertTrue(MoMoService::verifyIpn($callback));
        $this->postJson(route('api.payments.momo.ipn'), $callback)
            ->assertOk()
            ->assertJsonPath('resultCode', 0);

        $this->assertSame('dang_xu_ly', $order->fresh()->trang_thai);
        $this->assertSame('MOMO-TXN-200', $order->thanhToan()->firstOrFail()->ma_giao_dich);
        $this->assertSame(4, $book->khoHang()->firstOrFail()->fresh()->so_luong_ton);
    }

    public function test_expired_vnpay_callback_cancels_order_and_releases_reserved_stock(): void
    {
        Config::set('services.vnpay.hash_secret', 'vnpay-test-secret');
        Config::set('services.vnpay.tmn_code', 'BOOKBOX01');
        [$order, $book] = $this->createOnlineOrder('VNPay');
        $order->update(['thanh_toan_het_han_at' => now()->subMinute()]);

        $this->postJson(route('api.payments.vnpay.ipn'), $this->signedVnpayCallback($order, '5000000', 'TXN-300'))
            ->assertOk()
            ->assertJsonPath('RspCode', '02');

        $this->assertSame('da_huy', $order->fresh()->trang_thai);
        $this->assertSame('that_bai', $order->thanhToan()->firstOrFail()->trang_thai);
        $this->assertSame(0, $book->khoHang()->firstOrFail()->fresh()->so_luong_dat_truoc);
        $this->assertSame(5, $book->khoHang()->firstOrFail()->fresh()->so_luong_ton);
    }

    public function test_vietqr_signature_is_rendered_and_admin_must_reconcile_it(): void
    {
        Config::set('services.payments.demo_enabled', false);
        Config::set('services.vietqr.signature_key', 'vietqr-test-secret');
        Config::set('services.vietqr.bank_id', '970422');
        Config::set('services.vietqr.account_no', '1234567890');
        Config::set('services.vietqr.account_name', 'BOOK BOX');
        [$order, $book] = $this->createOnlineOrder('BankTransfer');
        $signature = VietQRService::signatureForOrder($order->ma_don_hang, 50000);

        $this->actingAs($order->nguoiDung)
            ->get(route('checkout.payment.start', $order->ma_don_hang))
            ->assertOk()
            ->assertViewIs('customer.payment-qr')
            ->assertViewHas('signature', $signature);

        $this->assertSame('cho_thanh_toan', $order->thanhToan()->firstOrFail()->trang_thai);
        $this->assertSame(1, $book->khoHang()->firstOrFail()->fresh()->so_luong_dat_truoc);

        /** @var NguoiDung $admin */
        $admin = NguoiDung::factory()->createOne();
        $role = VaiTro::create(['ten_vai_tro' => 'admin']);
        $admin->vaiTro()->attach($role);
        $admin->forceFill([
            'admin_totp_secret' => 'JBSWY3DPEHPK3PXP',
            'admin_totp_confirmed_at' => now(),
        ])->save();

        $this->actingAs($admin)->withSession(['admin_2fa_verified_user' => $admin->id])
            ->from(route('admin.orders.show', $order->id))
            ->post(route('admin.orders.payment.confirmBankTransfer', $order->id), [
                'so_tien' => 50000,
                'ma_giao_dich' => 'BANK-TXN-500',
                'signature' => str_repeat('0', 64),
            ])
            ->assertSessionHas('error');

        $this->assertSame('cho_thanh_toan', $order->thanhToan()->firstOrFail()->trang_thai);
        $this->assertSame('cho_xu_ly', $order->fresh()->trang_thai);

        $this->post(route('admin.orders.payment.confirmBankTransfer', $order->id), [
            'so_tien' => 50000,
            'ma_giao_dich' => 'BANK-TXN-500',
            'signature' => $signature,
        ])
            ->assertSessionHas('success');

        $this->assertSame('da_thanh_toan', $order->thanhToan()->firstOrFail()->trang_thai);
        $this->assertSame('dang_xu_ly', $order->fresh()->trang_thai);
        $this->assertSame(4, $book->khoHang()->firstOrFail()->fresh()->so_luong_ton);
        $this->assertSame(0, $book->khoHang()->firstOrFail()->fresh()->so_luong_dat_truoc);
    }

    #[DataProvider('demoPaymentMethods')]
    public function test_demo_payment_confirms_method_and_notifies_customer_and_admin(string $method): void
    {
        Config::set('services.payments.demo_enabled', true);
        [$order, $book] = $this->createOnlineOrder($method);
        $admin = NguoiDung::factory()->createOne();
        $role = VaiTro::create(['ten_vai_tro' => 'admin']);
        $admin->vaiTro()->attach($role);

        $this->actingAs($order->nguoiDung)
            ->get(route('checkout.payment.start', $order->ma_don_hang))
            ->assertViewIs('customer.payment-demo')
            ->assertViewHas('paymentMethod', $method);

        $this->post(route('checkout.payment.demo', $order->ma_don_hang))
            ->assertRedirect(route('customer.orders.show', $order->id))
            ->assertSessionHas('success');

        $this->assertSame('dang_xu_ly', $order->fresh()->trang_thai);
        $this->assertSame('da_thanh_toan', $order->thanhToan()->firstOrFail()->trang_thai);
        $this->assertSame(4, $book->khoHang()->firstOrFail()->fresh()->so_luong_ton);
        $this->assertDatabaseCount('notifications', 2);

        $customerNotification = $order->nguoiDung->notifications()->firstOrFail();
        $adminNotification = $admin->notifications()->firstOrFail();
        $this->assertSame(PaymentReceivedNotification::class, $customerNotification->type);
        $this->assertSame($method, $customerNotification->data['payment_method']);
        $this->assertSame(true, $adminNotification->data['is_demo']);
    }

    public static function demoPaymentMethods(): array
    {
        return [
            'momo' => ['MoMo'],
            'vietqr' => ['BankTransfer'],
        ];
    }

    public function test_demo_payment_cannot_pay_another_users_order(): void
    {
        Config::set('services.payments.demo_enabled', true);
        [$order] = $this->createOnlineOrder('MoMo');
        /** @var NguoiDung $otherCustomer */
        $otherCustomer = NguoiDung::factory()->createOne();

        $this->actingAs($otherCustomer)
            ->post(route('checkout.payment.demo', $order->ma_don_hang))
            ->assertNotFound();

        $this->assertSame('cho_thanh_toan', $order->thanhToan()->firstOrFail()->trang_thai);
        $this->assertDatabaseCount('notifications', 0);
    }

    public function test_demo_payment_is_hidden_when_demo_mode_is_disabled(): void
    {
        Config::set('services.payments.demo_enabled', false);
        [$order] = $this->createOnlineOrder('MoMo');

        $this->actingAs($order->nguoiDung)
            ->post(route('checkout.payment.demo', $order->ma_don_hang))
            ->assertNotFound();

        $this->assertSame('cho_thanh_toan', $order->thanhToan()->firstOrFail()->trang_thai);
    }

    private function createOnlineOrder(string $method): array
    {
        $customer = NguoiDung::factory()->createOne();
        $book = Sach::create([
            'tieu_de' => 'Payment test book',
            'duong_dan_tinh' => 'payment-test-book-'.str()->random(8),
            'gia_ban' => 50000,
            'dang_hoat_dong' => true,
        ]);
        $book->khoHang()->create(['so_luong_ton' => 5, 'so_luong_dat_truoc' => 1]);
        $order = DonHang::create([
            'ma_don_hang' => 'PAY-'.str()->random(10),
            'id_nguoi_dung' => $customer->id,
            'tong_tien' => 50000,
            'so_tien_giam_gia' => 0,
            'thanh_tien' => 50000,
            'trang_thai' => 'cho_xu_ly',
            'da_giu_ton' => true,
            'thanh_toan_het_han_at' => now()->addMinutes(15),
        ]);
        ChiTietDonHang::create([
            'id_don_hang' => $order->id,
            'id_sach' => $book->id,
            'don_gia' => 50000,
            'so_luong' => 1,
            'thanh_tien' => 50000,
        ]);
        $order->thanhToan()->create([
            'phuong_thuc_thanh_toan' => $method,
            'so_tien' => 50000,
            'trang_thai' => 'cho_thanh_toan',
        ]);

        return [$order, $book];
    }

    private function signedVnpayCallback(DonHang $order, string $amount, string $transactionId): array
    {
        $data = [
            'vnp_Amount' => $amount,
            'vnp_OrderInfo' => 'Thanh toan don hang '.$order->ma_don_hang,
            'vnp_ResponseCode' => '00',
            'vnp_TmnCode' => 'BOOKBOX01',
            'vnp_TransactionNo' => $transactionId,
            'vnp_TransactionStatus' => '00',
            'vnp_TxnRef' => $order->ma_don_hang,
        ];
        ksort($data);
        $hashData = collect($data)
            ->map(fn (string $value, string $key): string => urlencode($key).'='.urlencode($value))
            ->implode('&');
        $data['vnp_SecureHash'] = hash_hmac('sha512', $hashData, 'vnpay-test-secret');

        return $data;
    }

    private function momoSignature(array $payload): string
    {
        $signatureData = implode('&', [
            'accessKey=momo-access',
            'amount='.$payload['amount'],
            'extraData='.$payload['extraData'],
            'message='.$payload['message'],
            'orderId='.$payload['orderId'],
            'orderInfo='.$payload['orderInfo'],
            'orderType='.$payload['orderType'],
            'partnerCode='.$payload['partnerCode'],
            'payType='.$payload['payType'],
            'requestId='.$payload['requestId'],
            'responseTime='.$payload['responseTime'],
            'resultCode='.$payload['resultCode'],
            'transId='.$payload['transId'],
        ]);

        return hash_hmac('sha256', $signatureData, 'momo-test-secret');
    }
}
