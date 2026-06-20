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
        $this->get('http://kasiro.com/')
            ->assertOk()
            ->assertSee('Kasiro');
    }

    public function test_landing_shows_hero_text(): void
    {
        $this->get('http://kasiro.com/')
            ->assertOk()
            ->assertSee('Mulai Gratis')
            ->assertSee('POS bermerek');
    }

    public function test_landing_shows_register_and_login_links_for_guests(): void
    {
        $this->get('http://kasiro.com/')
            ->assertOk()
            ->assertSee('Daftar Gratis')
            ->assertSee('Masuk');
    }

    public function test_landing_shows_dashboard_link_for_authenticated_user(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('http://kasiro.com/')
            ->assertOk()
            ->assertSee('Dashboard')
            ->assertSee('Buat Toko');
    }

    public function test_landing_shows_published_templates(): void
    {
        Template::factory()->create([
            'name'         => 'Kopi Hits',
            'is_published' => true,
        ]);

        $this->get('http://kasiro.com/')
            ->assertOk()
            ->assertSee('Kopi Hits');
    }

    public function test_landing_hides_unpublished_templates(): void
    {
        Template::factory()->create([
            'name'         => 'Draft Template',
            'is_published' => false,
        ]);

        $this->get('http://kasiro.com/')
            ->assertOk()
            ->assertDontSee('Draft Template');
    }

    public function test_landing_shows_features_section(): void
    {
        $this->get('http://kasiro.com/')
            ->assertOk()
            ->assertSee('POS Bermerek')
            ->assertSee('Manajemen Karyawan')
            ->assertSee('Laporan Penjualan');
    }

    public function test_landing_shows_how_it_works_steps(): void
    {
        $this->get('http://kasiro.com/')
            ->assertOk()
            ->assertSee('Daftar akun')
            ->assertSee('Pilih template')
            ->assertSee('Mulai bertransaksi');
    }
}
