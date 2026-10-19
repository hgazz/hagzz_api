<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class MobileCatalogTest extends TestCase
{
    public function test_public_mobile_catalog_exposes_fifteen_academies_and_fortyfive_trainings_per_country(): void
    {
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite'); // No live tables; this endpoint must not read or change real bookings.
        foreach (['eg' => 'EGP', 'qa' => 'QAR'] as $country => $currency) {
            DB::enableQueryLog(); DB::flushQueryLog();
            $response = $this->getJson('/api/mobile-catalog?country='.$country)->assertOk()
                ->assertJsonCount(15, 'data.academies')->assertJsonPath('data.trainings_count', 45)
                ->assertJsonPath('data.bookable', false)->assertJsonPath('data.is_demo', true);
            foreach ($response->json('data.academies') as $academy) {
                $this->assertSame($currency, $academy['currency']);
                $this->assertCount(3, $academy['trainings']);
                $this->assertFalse($academy['bookable']);
                $this->assertArrayNotHasKey('id', $academy);
                foreach ($academy['trainings'] as $training) {
                    $this->assertFalse($training['bookable']);
                    $this->assertArrayNotHasKey('id', $training);
                    $this->assertSame($currency, $training['currency']);
                    $this->assertNotEmpty($training['timezone']);
                }
            }
            $this->assertSame([], DB::getQueryLog()); DB::disableQueryLog();
        }
    }

    public function test_invalid_country_is_rejected_and_default_is_egypt(): void
    {
        $this->getJson('/api/mobile-catalog?country=xx')->assertUnprocessable();
        $this->getJson('/api/mobile-catalog')->assertOk()->assertJsonPath('data.country', 'eg');
        $this->postJson('/api/mobile-catalog')->assertStatus(405);
    }
}
