<?php

namespace App\Observers;

use App\Jobs\GenerateTenantScreenshot;
use App\Models\Product;
use App\Support\TenantContext;

class ProductObserver
{
    public function created(Product $product): void
    {
        $this->dispatchFor($product);
    }

    public function updated(Product $product): void
    {
        // Only re-capture when something visible in the POS grid changes.
        if ($product->wasChanged(['name', 'image_path', 'is_active', 'price'])) {
            $this->dispatchFor($product);
        }
    }

    public function deleted(Product $product): void
    {
        $this->dispatchFor($product);
    }

    private function dispatchFor(Product $product): void
    {
        $tenant = app(TenantContext::class)->get();

        if (! $tenant) {
            return;
        }

        if (app()->environment('local')) {
            GenerateTenantScreenshot::dispatchSync($tenant);
        } else {
            GenerateTenantScreenshot::dispatch($tenant)->delay(now()->addSeconds(3));
        }
    }
}
