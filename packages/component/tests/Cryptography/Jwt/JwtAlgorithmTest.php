<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Tests\Cryptography\Jwt;

use PhoneBurner\Pinch\Component\Cryptography\Jwt\JwtAlgorithm;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class JwtAlgorithmTest extends TestCase
{
    #[Test]
    #[DataProvider('providesSupportedAlgorithms')]
    public function supportedAlgorithmsExist(JwtAlgorithm $algorithm, string $expected_value): void
    {
        self::assertSame($expected_value, $algorithm->value);
    }

    #[Test]
    public function canCreateFromString(): void
    {
        self::assertSame(JwtAlgorithm::RS256, JwtAlgorithm::from('RS256'));
        self::assertSame(JwtAlgorithm::HS256, JwtAlgorithm::from('HS256'));
        self::assertSame(JwtAlgorithm::EdDSA, JwtAlgorithm::from('EdDSA'));
    }

    #[Test]
    public function rejectsUnsupportedAlgorithm(): void
    {
        $this->expectException(\ValueError::class);

        JwtAlgorithm::from('none');
    }

    #[Test]
    public function rejectsInsecureAlgorithms(): void
    {
        $this->expectException(\ValueError::class);

        JwtAlgorithm::from('HS1');
    }

    public static function providesSupportedAlgorithms(): \Generator
    {
        yield 'RS256' => [JwtAlgorithm::RS256, 'RS256'];
        yield 'HS256' => [JwtAlgorithm::HS256, 'HS256'];
        yield 'EdDSA' => [JwtAlgorithm::EdDSA, 'EdDSA'];
    }
}
