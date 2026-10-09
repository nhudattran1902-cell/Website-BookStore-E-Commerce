<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\DashboardController;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DashboardWithoutChatTableTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_works_when_chat_table_is_missing(): void
    {
        Schema::dropIfExists('tin_nhan_chat');

        $this->assertFalse(Schema::hasTable('tin_nhan_chat'));

        $view = (new DashboardController)->index();

        $this->assertSame('admin.dashboard', $view->name());
    }
}
