<?php

namespace Tests\Feature;

use App\Models\Template;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LandingPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_access_landing_page(): void
    {
        $this->get('http://kasiro.my.id/')
            ->assertOk()
            ->assertSee('Kasiro');
    }

    public function test_landing_shows_hero_text(): void
    {
        $this->get('http://kasiro.my.id/')
            ->assertOk()
            ->assertSee('Transaksi Mudah')
            ->assertSee('Usaha Terarah')
            ->assertSee('Buat Sekarang');
    }

    public function test_landing_shows_login_link_for_guests(): void
    {
        $this->get('http://kasiro.my.id/')
            ->assertOk()
            ->assertSee('Login');
    }

    public function test_landing_shows_dashboard_link_for_authenticated_user(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('http://kasiro.my.id/')
            ->assertOk()
            ->assertSee('Dashboard');
    }

    public function test_landing_shows_published_templates(): void
    {
        Template::factory()->create([
            'name'         => 'Kopi Hits',
            'is_published' => true,
        ]);

        $this->get('http://kasiro.my.id/')
            ->assertOk()
            ->assertSee('Kopi Hits');
    }

    public function test_landing_hides_unpublished_templates(): void
    {
        Template::factory()->create([
            'name'         => 'Draft Template',
            'is_published' => false,
        ]);

        $this->get('http://kasiro.my.id/')
            ->assertOk()
            ->assertDontSee('Draft Template');
    }

    public function test_landing_shows_main_sections(): void
    {
        $this->get('http://kasiro.my.id/')
            ->assertOk()
            ->assertSee('Template Kasir')
            ->assertSee('3 Hal yang membuat KASIRO berbeda')
            ->assertSee('Pertanyaan Umum')
            ->assertSee('Kami Mendengar Anda!');
    }
}
