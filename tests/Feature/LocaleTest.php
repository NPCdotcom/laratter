<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocaleTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_update_locale_in_session(): void
    {
        $response = $this->from('/')->post(route('locale.update'), [
            'locale' => 'en_US',
        ]);

        $response->assertRedirect('/');
        $response->assertSessionHas('locale', 'en_US');

        $this->get('/')
            ->assertOk()
            ->assertSee('lang="en-US"', false);
    }

    public function test_authenticated_user_locale_is_persisted(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->from(route('tweets.index'))->post(route('locale.update'), [
            'locale' => 'es',
        ]);

        $response->assertRedirect(route('tweets.index'));
        $response->assertSessionHas('locale', 'es');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'locale' => 'es',
        ]);

        $this->actingAs($user->fresh())
            ->get(route('tweets.index'))
            ->assertOk()
            ->assertSee('Lista de tweets', false);
    }

    public function test_invalid_locale_is_rejected(): void
    {
        $response = $this->from('/')->post(route('locale.update'), [
            'locale' => 'xx_INVALID',
        ]);

        $response->assertRedirect('/');
        $response->assertSessionHasErrors('locale');
        $response->assertSessionMissing('locale');
    }

    public function test_user_preferred_locale_is_used_when_session_missing(): void
    {
        $user = User::factory()->create([
            'locale' => 'fr',
        ]);

        $this->actingAs($user)
            ->get(route('tweets.index'))
            ->assertOk()
            ->assertSee('Liste des tweets', false);
    }

    public function test_arabic_layout_uses_rtl_direction(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->withSession(['locale' => 'ar'])
            ->get(route('tweets.index'))
            ->assertOk()
            ->assertSee('dir="rtl"', false)
            ->assertSee('lang="ar"', false);
    }
}
