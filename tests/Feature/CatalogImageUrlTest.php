<?php

namespace Tests\Feature;

use App\Models\Coach;
use App\Models\Gallery;
use Tests\TestCase;

class CatalogImageUrlTest extends TestCase
{
    public function test_new_gallery_uploads_use_the_configured_current_storage(): void
    {
        config(['services.storage.url' => 'https://media.example.test/', 'app.url' => 'https://api.example.test']);
        $image = new Gallery(['image' => '65afc9133f9a1.12345678.jpg']);
        $this->assertSame('https://media.example.test/images/gallery/65afc9133f9a1.12345678.jpg', $image->image);
    }

    public function test_coach_images_use_current_storage_and_preserve_null(): void
    {
        config(['services.storage.url' => 'https://media.example.test/']);
        $coach = new Coach;
        $coach->setRawAttributes(['image' => '65afc9133f9a1.12345678.jpg']);
        $this->assertSame('https://media.example.test/'.trim(Coach::PATH, '/').'/65afc9133f9a1.12345678.jpg', $coach->image);
        $coach->setRawAttributes(['image' => null]);
        $this->assertNull($coach->image);
    }

    public function test_full_image_urls_are_not_prefixed_with_retired_s3_storage(): void
    {
        $url = 'https://images.example.test/approved-photo.jpg';
        $gallery = new Gallery(['image' => $url]);
        $coach = new Coach; $coach->setRawAttributes(['image' => $url]);
        $this->assertSame($url, $gallery->image);
        $this->assertSame($url, $coach->image);
    }

    public function test_retired_and_missing_images_use_existing_safe_placeholders(): void
    {
        config(['app.url' => 'https://api.example.test']);
        $gallery = new Gallery(['image' => 'https://bokit-app.s3.eu-north-1.amazonaws.com/old.jpg']);
        $this->assertSame('https://api.example.test/fallbacks/academy.svg', $gallery->image);
        $gallery->image = null;
        $this->assertSame('https://api.example.test/fallbacks/academy.svg', $gallery->image);
    }
}
