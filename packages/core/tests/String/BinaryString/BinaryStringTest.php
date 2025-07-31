<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Tests\String\BinaryString;

use PhoneBurner\Pinch\String\BinaryString\BinaryString;
use PhoneBurner\Pinch\String\Encoding\Encoding;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(BinaryString::class)]
final class BinaryStringTest extends TestCase
{
    #[Test]
    public function interfaceDefaultsToBase64UrlEncoding(): void
    {
        self::assertSame(Encoding::Base64Url, BinaryString::DEFAULT_ENCODING);
    }
}
