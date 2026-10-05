<?php

namespace Tests\Feature\Settings;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Tests\TestCase;

class SocialSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_social_settings_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('settings.social'))
            ->assertOk()
            ->assertSee(__('ui.settings.social_heading'));
    }

    public function test_can_update_auto_accept_follows(): void
    {
        $user = User::factory()->create(['auto_accept_follows' => true]);

        $this->actingAs($user);

        Volt::test('settings.social')
            ->set('auto_accept_follows', false)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertFalse($user->fresh()->auto_accept_follows);
    }
}
