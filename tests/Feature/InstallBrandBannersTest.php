<?php

namespace Tests\Feature;

use App\Models\Banner;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class InstallBrandBannersTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:', 'app.url' => 'https://api.example.test']);
        DB::purge('sqlite');
        Schema::create('banners', function (Blueprint $table) {
            $table->id(); $table->string('logo'); $table->string('status'); $table->string('country'); $table->timestamps();
        });
    }

    public function test_preview_makes_no_database_changes(): void
    {
        $this->artisan('hagzz:brand-banners')->expectsOutputToContain('Preview only')->assertSuccessful();
        $this->assertSame(0, Banner::count());
    }

    public function test_apply_is_repeatable_and_preserves_existing_campaigns_and_deactivations(): void
    {
        $old = Banner::create(['country' => 'eg', 'logo' => 'https://media.example.test/approved-campaign.jpg', 'status' => 'active']);
        $this->artisan('hagzz:brand-banners --apply')->assertSuccessful();
        $this->assertSame(7, Banner::count());
        $this->assertSame(3, Banner::visibleIn('qa')->count());
        $this->assertSame('active', $old->fresh()->status);
        $banner = Banner::where('country', 'qa')->first(); $banner->update(['status' => 'inactive']);
        $this->artisan('hagzz:brand-banners --apply')->assertSuccessful();
        $this->assertSame(7, Banner::count());
        $this->assertSame('inactive', $banner->fresh()->status);
        foreach (Banner::where('id', '!=', $old->id)->get() as $row) {
            $this->assertStringStartsWith('https://api.example.test/media/hagzz/brand-v1/', $row->logo);
            $this->assertFileExists(public_path(parse_url($row->logo, PHP_URL_PATH)));
        }
    }

    public function test_invalid_public_url_prevents_inserting_unusable_links(): void
    {
        config(['app.url' => 'not-a-url']);
        $this->artisan('hagzz:brand-banners --apply')->assertFailed();
        $this->assertSame(0, Banner::count());
    }
}
