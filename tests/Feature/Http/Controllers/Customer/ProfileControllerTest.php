<?php

namespace Tests\Feature\Http\Controllers\Customer;

use App\Models\NguoiDung;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_replace_avatar_and_see_it_in_the_navigation(): void
    {
        Storage::fake('public');
        $user = NguoiDung::factory()->createOne(['anh_dai_dien' => 'avatars/old-avatar.png']);
        Storage::disk('public')->put('avatars/old-avatar.png', 'old image');

        $response = $this->actingAs($user)->put(route('customer.profile.update'), [
            'ho_ten' => $user->ho_ten,
            'so_dien_thoai' => $user->so_dien_thoai,
            'anh_dai_dien' => UploadedFile::fake()->image('new-avatar.png'),
        ]);

        $response->assertRedirect();
        $user->refresh();
        $this->assertStringStartsWith('avatars/', $user->anh_dai_dien);
        Storage::disk('public')->assertExists($user->anh_dai_dien);
        Storage::disk('public')->assertMissing('avatars/old-avatar.png');

        $this->get(route('pages.faq'))->assertSee(Storage::disk('public')->url($user->anh_dai_dien), false);
    }

    public function test_invalid_avatar_is_rejected_without_replacing_current_avatar(): void
    {
        Storage::fake('public');
        $user = NguoiDung::factory()->createOne(['anh_dai_dien' => 'avatars/current-avatar.png']);
        Storage::disk('public')->put('avatars/current-avatar.png', 'current image');

        $response = $this->actingAs($user)->put(route('customer.profile.update'), [
            'ho_ten' => $user->ho_ten,
            'so_dien_thoai' => $user->so_dien_thoai,
            'anh_dai_dien' => UploadedFile::fake()->create('not-an-image.txt', 10, 'text/plain'),
        ]);

        $response->assertSessionHasErrors('anh_dai_dien');
        $user->refresh();
        $this->assertSame('avatars/current-avatar.png', $user->anh_dai_dien);
        Storage::disk('public')->assertExists('avatars/current-avatar.png');
    }
}
