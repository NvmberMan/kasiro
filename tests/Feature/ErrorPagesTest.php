<?php

namespace Tests\Feature;

use Tests\TestCase;

class ErrorPagesTest extends TestCase
{
    public function test_all_error_views_compile(): void
    {
        foreach (['401', '403', '404', '419', '429', '500', '503'] as $code) {
            $html = view("errors.{$code}")->render();
            $this->assertStringContainsString($code, $html);
            $this->assertStringContainsString('Kembali ke Beranda', $html);
        }
    }

    public function test_missing_page_returns_custom_404(): void
    {
        $this->get('http://kasiro.my.id/halaman-yang-tidak-ada')
            ->assertNotFound()
            ->assertSee('Halaman Tidak Ditemukan');
    }
}
