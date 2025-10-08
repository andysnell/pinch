<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Tests\IpAddress;

use Generator;
use PhoneBurner\Pinch\Component\IpAddress\Exception\InvalidIpAddress;
use PhoneBurner\Pinch\Component\IpAddress\IpAddress;
use PhoneBurner\Pinch\Component\IpAddress\IpAddressType;
use PhoneBurner\Pinch\Component\Tests\Fixtures\IpAddressTestStruct;
use PhoneBurner\Pinch\Uuid\Uuid;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class IpAddressTest extends TestCase
{
    #[DataProvider('provideValidAddresses')]
    #[Test]
    public function marshallReturnsValueFromTrueIp(IpAddressTestStruct $address): void
    {
        $data = [
            'HTTP_TRUE_CLIENT_IP' => $address->value,
        ];
        $sut = IpAddress::marshall($data);
        self::assertSame($address->value, (string)$sut);
        self::assertSame($address->value, $sut?->value);
        self::assertSame($address->type, $sut->type);
        self::assertSame($address->is_private, $sut->is_private);
        self::assertSame($address->is_reserved, $sut->is_reserved);
        self::assertEquals(\unserialize(\serialize($sut)), $sut);
    }

    #[DataProvider('provideValidAddresses')]
    #[Test]
    public function marshallReturnsValueFromForwardedId(IpAddressTestStruct $address): void
    {
        $data = [
            'HTTP_TRUE_CLIENT_IP' => $address->value . ', 127.0.0.1',
        ];
        $sut = IpAddress::marshall($data);
        self::assertSame($address->value, (string)$sut);
        self::assertSame($address->value, $sut?->value);
        self::assertSame($address->type, $sut->type);
        self::assertSame($address->is_private, $sut->is_private);
        self::assertSame($address->is_reserved, $sut->is_reserved);
        self::assertEquals(\unserialize(\serialize($sut)), $sut);
    }

    #[DataProvider('provideValidAddresses')]
    #[Test]
    public function marshallReturnsValueFromRemoteIp(IpAddressTestStruct $address): void
    {
        $data = [
            'REMOTE_ADDR' => $address->value,
        ];
        $sut = IpAddress::marshall($data);
        self::assertSame($address->value, (string)$sut);
        self::assertSame($address->value, $sut?->value);
        self::assertSame($address->type, $sut->type);
        self::assertSame($address->is_private, $sut->is_private);
        self::assertSame($address->is_reserved, $sut->is_reserved);
        self::assertEquals(\unserialize(\serialize($sut)), $sut);
    }

    #[Test]
    public function marshallReturnsNullOnSadPath(): void
    {
        $sut = IpAddress::marshall([]);
        self::assertNull($sut);
    }

    #[DataProvider('provideValidAddresses')]
    #[Test]
    public function makeReturnsValue(IpAddressTestStruct $address): void
    {
        $sut = IpAddress::make($address->value);
        self::assertSame($address->value, (string)$sut);
        self::assertSame($address->value, $sut->value);
        self::assertSame($address->type, $sut->type);
        self::assertSame($address->is_private, $sut->is_private);
        self::assertSame($address->is_reserved, $sut->is_reserved);
        self::assertEquals(\unserialize(\serialize($sut)), $sut);
    }

    #[DataProvider('provideInvalidAddresses')]
    #[Test]
    public function makeThrowsInvalidArgument(string $address): void
    {
        $this->expectException(InvalidIpAddress::class);
        IpAddress::make($address);
    }

    #[DataProvider('provideValidAddresses')]
    #[Test]
    public function tryFromReturnsValueFromString(IpAddressTestStruct $address): void
    {
        $sut = IpAddress::tryFrom($address->value);
        self::assertNotNull($sut);
        self::assertSame($address->value, (string)$sut);
        self::assertSame($address->value, $sut->value);
        self::assertSame($address->type, $sut->type);
        self::assertSame($address->is_private, $sut->is_private);
        self::assertSame($address->is_reserved, $sut->is_reserved);
        self::assertEquals(\unserialize(\serialize($sut)), $sut);
    }

    #[DataProvider('provideValidAddresses')]
    #[Test]
    public function tryFromReturnsValueFromSelf(IpAddressTestStruct $address): void
    {
        $address = IpAddress::make($address->value);
        self::assertSame($address, IpAddress::tryFrom($address));
    }

    #[DataProvider('provideValidAddresses')]
    #[Test]
    public function tryFromReturnsValueFromStringable(IpAddressTestStruct $address): void
    {
        $value = IpAddress::tryFrom(new readonly class ($address->value) implements \Stringable {
            public function __construct(private string $address)
            {
            }

            public function __toString(): string
            {
                return $this->address;
            }
        });

        self::assertNotNull($value);
        self::assertSame($address->value, (string)$value);
    }

    #[DataProvider('provideInvalidAddresses')]
    #[DataProvider('provideNonStringInvalidAddresses')]
    #[Test]
    public function tryFromReturnsNullWhenInvalid(mixed $address): void
    {
        self::assertNull(IpAddress::tryFrom($address));
    }

    public static function provideValidAddresses(): Generator
    {
        yield [new IpAddressTestStruct('127.0.0.1', IpAddressType::IPv4, false, true)];
        yield [new IpAddressTestStruct('0.0.0.0', IpAddressType::IPv4, false, true)];
        yield [new IpAddressTestStruct('172.16.17.32', IpAddressType::IPv4, true, false)];
        yield [new IpAddressTestStruct('10.0.0.0', IpAddressType::IPv4, true, false)];
        yield [new IpAddressTestStruct('172.16.58.3', IpAddressType::IPv4, true, false)];
        yield [new IpAddressTestStruct('192.168.3.11', IpAddressType::IPv4, true, false)];
        yield [new IpAddressTestStruct('106.112.47.159', IpAddressType::IPv4, false, false)];
        yield [new IpAddressTestStruct('15.146.108.213', IpAddressType::IPv4, false, false)];
        yield [new IpAddressTestStruct('187.73.69.189', IpAddressType::IPv4, false, false)];
        yield [new IpAddressTestStruct('74.240.149.252', IpAddressType::IPv4, false, false)];
        yield [new IpAddressTestStruct('109.247.203.56', IpAddressType::IPv4, false, false)];
        yield [new IpAddressTestStruct('8.165.230.95', IpAddressType::IPv4, false, false)];
        yield [new IpAddressTestStruct('35.104.24.140', IpAddressType::IPv4, false, false)];
        yield [new IpAddressTestStruct('191.178.203.40', IpAddressType::IPv4, false, false)];
        yield [new IpAddressTestStruct('178.245.90.92', IpAddressType::IPv4, false, false)];
        yield [new IpAddressTestStruct('2.52.178.30', IpAddressType::IPv4, false, false)];
        yield [new IpAddressTestStruct('0d65:08ca:fadf:10d3:8a3c:3efa:422d:0df7', IpAddressType::IPv6, false, false)];
        yield [new IpAddressTestStruct('94a9:fbc4:4883:5be2:31d3:642f:b9c3:93dc', IpAddressType::IPv6, false, false)];
        yield [new IpAddressTestStruct('cfe1:bb61:368b:f6a3:251f:502d:5a12:24b9', IpAddressType::IPv6, false, false)];
        yield [new IpAddressTestStruct('fefa:8895:88fc:085b:77b0:7ca8:b096:3680', IpAddressType::IPv6, false, false)];
        yield [new IpAddressTestStruct('fcfa:8895:88fc:085b:77b0:7ca8:b096:3680', IpAddressType::IPv6, true, false)];
        yield [new IpAddressTestStruct('fdfa:8895:88fc:085b:77b0:7ca8:b096:3680', IpAddressType::IPv6, true, false)];
        yield [new IpAddressTestStruct('4814:d98e:8a2a:791d:2c4d:65f8:659b:6ad5', IpAddressType::IPv6, false, false)];
        yield [new IpAddressTestStruct('68ab:8fbb:8264:3cf4:8a01:0c49:4b30:2b6b', IpAddressType::IPv6, false, false)];
        yield [new IpAddressTestStruct('3e2c:0c16:fce3:d292:0404:3bce:1b6a:f43e', IpAddressType::IPv6, false, false)];
        yield [new IpAddressTestStruct('0f57:1e3f:b0b9:f8b4:4fd2:05ae:baf2:352c', IpAddressType::IPv6, false, false)];
        yield [new IpAddressTestStruct('93b4:ca80:1611:a65b:b643:b9aa:faff:357b', IpAddressType::IPv6, false, false)];
        yield [new IpAddressTestStruct('9a9e:6857:7f05:f213:1391:2093:c1f7:2d92', IpAddressType::IPv6, false, false)];
        yield [new IpAddressTestStruct('::', IpAddressType::IPv6, false, true)];
        yield [new IpAddressTestStruct('::0', IpAddressType::IPv6, false, true)];
    }

    public static function provideInvalidAddresses(): Generator
    {
        foreach (
            [
            'not_an_ip',
            Uuid::random()->toString(),
            '192-168-0-1',
            '255.255.255.256',
            ] as $address
        ) {
            yield $address => [$address];
        }
    }

    public static function provideNonStringInvalidAddresses(): Generator
    {
        yield 'null' => [null];
        yield 'empty_string' => [''];
        yield 'empty_array' => [[]];
        yield 'int' => [234];
        yield 'float' => [234.23];
        yield 'bool_true' => [true];
        yield 'bool_false' => [false];
        yield 'object' => [new \stdClass()];
    }
}
