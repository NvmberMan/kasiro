<?php

namespace App\Console\Commands;

use App\Jobs\GenerateTemplateScreenshot;
use App\Models\Product;
use App\Models\Template;
use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class RefreshTemplateScreenshots extends Command
{
    protected $signature = 'templates:screenshots {--slug= : Only refresh this template slug} {--queue : Dispatch to the queue instead of running inline}';

    protected $description = 'Generate POS preview screenshots for published templates';

    public function handle(): int
    {
        $query = Template::published();

        if ($slug = $this->option('slug')) {
            $query->where('slug', $slug);
        }

        $templates = $query->get();

        if ($templates->isEmpty()) {
            $this->warn('No matching published templates found.');

            return self::SUCCESS;
        }

        // Active tenants that actually have a catalog can serve as preview donors.
        $donors = $this->donorPool();

        if ($donors->isEmpty()) {
            $this->error('No active tenant with products available to render previews.');

            return self::FAILURE;
        }

        foreach ($templates as $template) {
            $donor = $this->pickDonor($donors, $template);

            if ($this->option('queue')) {
                GenerateTemplateScreenshot::dispatch($template, $donor);
                $this->line("Queued: {$template->slug} (donor: {$donor->subdomain})");

                continue;
            }

            $this->line("Rendering: {$template->slug} (donor: {$donor->subdomain})...");
            GenerateTemplateScreenshot::dispatchSync($template, $donor);
            $this->info("  saved screenshots/template-{$template->slug}.jpg");
        }

        $this->info("Processed {$templates->count()} template(s).");

        return self::SUCCESS;
    }

    /**
     * Active tenants that have at least one active product, eligible as donors.
     *
     * @return Collection<int, Tenant>
     */
    private function donorPool(): Collection
    {
        return Tenant::active()
            ->orderBy('id')
            ->get()
            ->filter(fn (Tenant $t) => Product::withoutTenantScope()
                ->where('tenant_id', $t->id)
                ->where('is_active', true)
                ->exists());
    }

    /**
     * Prefer a donor that already uses this template (best visual match); fall
     * back to any populated active tenant.
     *
     * @param  Collection<int, Tenant>  $donors
     */
    private function pickDonor(Collection $donors, Template $template): Tenant
    {
        return $donors->firstWhere('template_id', $template->id)
            ?? $donors->first();
    }
}
