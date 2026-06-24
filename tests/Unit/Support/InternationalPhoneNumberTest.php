<?php

namespace Tests\Unit\Support;

use App\Support\InternationalPhoneNumber;
use PHPUnit\Framework\TestCase;

class InternationalPhoneNumberTest extends TestCase
{
    public function test_it_supports_the_released_mobile_apps_egypt_code(): void
    {
        $this->assertSame(
            '+201070809633',
            InternationalPhoneNumber::format('+2', '01070809633')
        );
    }

    public function test_it_supports_the_standard_egypt_code(): void
    {
        $this->assertSame(
            '+201070809633',
            InternationalPhoneNumber::format('+20', '01070809633')
        );
    }

    public function test_it_supports_qatar_numbers(): void
    {
        $this->assertSame(
            '+97455001234',
            InternationalPhoneNumber::format('+974', '55001234')
        );
    }

    public function test_it_supports_other_international_numbers(): void
    {
        $this->assertSame(
            '+966501234567',
            InternationalPhoneNumber::format('+966', '0501234567')
        );
    }
}
