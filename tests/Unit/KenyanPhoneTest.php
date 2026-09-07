<?php

namespace Tests\Unit;

use App\Support\KenyanPhone;
use PHPUnit\Framework\TestCase;

class KenyanPhoneTest extends TestCase
{
    public function test_it_normalizes_common_kenyan_formats(): void
    {
        $this->assertSame('254712345678', KenyanPhone::normalize('0712345678'));
        $this->assertSame('254112345678', KenyanPhone::normalize('0112345678'));
        $this->assertSame('254712345678', KenyanPhone::normalize('254712345678'));
        $this->assertSame('254712345678', KenyanPhone::normalize('+254712345678'));
        $this->assertSame('254712345678', KenyanPhone::normalize('0712 345 678'));
    }

    public function test_it_rejects_invalid_numbers(): void
    {
        $this->assertNull(KenyanPhone::normalize('12345'));
        $this->assertNull(KenyanPhone::normalize('07234567'));
        $this->assertNull(KenyanPhone::normalize('254812345678'));
    }

    public function test_it_formats_local_display(): void
    {
        $this->assertSame('0712345678', KenyanPhone::formatLocal('254712345678'));
    }
}
