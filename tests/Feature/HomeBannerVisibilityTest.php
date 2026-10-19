<?php

namespace Tests\Feature;

use App\Http\Controllers\Api\HomeController;
use App\Models\Banner;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class HomeBannerVisibilityTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:', 'cache.default' => 'array']);
        DB::purge('sqlite');
        Schema::create('banners', function (Blueprint $table) {
            $table->id(); $table->string('logo'); $table->string('status'); $table->string('country'); $table->timestamps();
        });
        Schema::create('sports', function (Blueprint $table) { $table->id(); $table->text('name'); $table->string('icon'); });
        Schema::create('trainings', function (Blueprint $table) { $table->id(); $table->unsignedBigInteger('address_id'); $table->boolean('active'); });
        Schema::create('addresses', function (Blueprint $table) { $table->id(); $table->unsignedBigInteger('country_id'); });
        foreach (['eg', 'qa'] as $country) {
            foreach (['active', 'inactive'] as $status) {
                Banner::create(['logo' => "https://media.example.test/{$country}-{$status}.jpg", 'status' => $status, 'country' => $country]);
            }
        }
    }

    public function test_visible_banners_respect_the_admin_country_and_status(): void
    {
        $this->assertSame(['https://media.example.test/eg-active.jpg'], Banner::visibleIn('EG')->get()->pluck('logo')->all());
        $this->assertSame(['https://media.example.test/qa-active.jpg'], Banner::visibleIn('qa')->get()->pluck('logo')->all());
        $this->assertCount(0, Banner::visibleIn('unknown')->get());
        $banner = Banner::visibleIn('eg')->first(); $banner->update(['status' => 'inactive']);
        $this->assertCount(0, Banner::visibleIn('eg')->get());
    }

    public function test_authenticated_home_uses_country_filtered_active_banners(): void
    {
        foreach ([1 => 'eg', 2 => 'qa'] as $id => $country) {
            $user = new User; $user->id = 99; $user->country_id = $id;
            $this->actingAs($user, 'api');
            $controller = $this->controller();
            $response = json_decode($controller->home(Request::create('/api/home'))->getContent(), true);
            $this->assertSame("https://media.example.test/{$country}-active.jpg", $response['data']['banners'][0]['logo']);
            $this->assertCount(1, $response['data']['banners']);
        }
    }

    public function test_guest_home_uses_country_filtered_active_banners(): void
    {
        $controller = $this->controller();
        $method = new \ReflectionMethod(HomeController::class, 'guestHomeData');
        $method->setAccessible(true);
        foreach (['eg', 'qa'] as $country) {
            $data = $method->invoke($controller, Request::create('/api/home'), $country);
            $this->assertSame("https://media.example.test/{$country}-active.jpg", $data['banners'][0]['logo']);
            $this->assertCount(1, $data['banners']);
        }
    }

    private function controller(): HomeController
    {
        return new class extends HomeController {
            protected function getUserSports() { return []; }
            public function getPartnersWithAuth(): array|Collection { return []; }
            protected function getUserTraining() { return []; }
            public function getPartnersGuest(): array|Collection { return []; }
            protected function getRandomTrainings() { return \App\Http\Resources\TrainingResource::collection(collect()); }
        };
    }
}
