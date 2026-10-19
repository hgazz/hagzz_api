<?php

namespace App\Console\Commands;

use App\Models\Banner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class InstallBrandBanners extends Command
{
    protected $signature = 'hagzz:brand-banners {--apply : Insert the reviewed brand banners; default is preview only}';
    protected $description = 'Preview or install the Hagzz carousel artwork for Egypt and Qatar without replacing existing campaigns';

    public function handle(): int
    {
        $base = rtrim(config('app.url'), '/');
        if (!filter_var($base, FILTER_VALIDATE_URL) || !in_array(parse_url($base, PHP_URL_SCHEME), ['http', 'https'], true)) {
            $this->error('APP_URL must be the public API URL before generating banner links.');
            return self::FAILURE;
        }
        $rows = [];
        foreach (['discover', 'swimming', 'padel'] as $name) {
            $path = "media/hagzz/brand-v1/{$name}.png";
            if (!is_file(public_path($path))) {
                $this->error("Missing artwork: {$path}");
                return self::FAILURE;
            }
            foreach (['eg', 'qa'] as $country) {
                $rows[] = ['country' => $country, 'logo' => $base.'/'.$path];
            }
        }
        $this->table(['Country', 'Image URL'], array_map(fn ($row) => array_values($row), $rows));
        if (!$this->option('apply')) {
            $this->info('Preview only. No database changes. Verify APP_URL and the artwork, then use --apply.');
            return self::SUCCESS;
        }
        $created = DB::transaction(function () use ($rows) {
            $count = 0;
            foreach ($rows as $row) {
                $banner = Banner::firstOrCreate($row, ['status' => 'active']);
                $count += (int) $banner->wasRecentlyCreated;
            }
            return $count;
        });
        $this->info("Created {$created} banner records. Existing banners and their activation settings were preserved.");
        return self::SUCCESS;
    }
}
