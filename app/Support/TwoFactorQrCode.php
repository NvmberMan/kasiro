<?php

namespace App\Support;

use App\Models\User;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorQrCode
{
    /**
     * Build the inline SVG QR code for the user's pending 2FA secret.
     * Returns an empty string when no secret is set.
     */
    public static function svg(User $user, int $size = 192): string
    {
        if (! $user->two_factor_secret) {
            return '';
        }

        $otpauthUrl = app(Google2FA::class)->getQRCodeUrl(
            (string) config('app.name'),
            (string) $user->email,
            (string) $user->two_factor_secret,
        );

        $renderer = new ImageRenderer(
            new RendererStyle($size, 0),
            new SvgImageBackEnd,
        );

        return (new Writer($renderer))->writeString($otpauthUrl);
    }
}
