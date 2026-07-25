<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class LogoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_log_out(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->assertTrue(Auth::check());

        $this->post(route('logout'))->assertRedirect(route('home'));

        $this->assertFalse(Auth::check());
    }
}
