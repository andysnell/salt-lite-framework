<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Tests\Domain\Ip;

use Generator;
use InvalidArgumentException;
use PhoneBurner\SaltLite\Framework\Domain\Ip\IpAddress;
use PhoneBurner\SaltLite\Framework\Domain\Ip\IpAddressType;
use PhoneBurner\SaltLite\Framework\Util\Helper\Uuid;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class IpAddressTest extends TestCase
{
    #[DataProvider('provideValidAddresses')]
    #[Test]
    public function marshall_returns_value_from_true_ip(string $address, IpAddressType $type): void
    {
        $data = [
            'HTTP_TRUE_CLIENT_IP' => $address,
        ];
        $sut = IpAddress::marshall($data);
        self::assertSame($address, (string)$sut);
        self::assertSame($address, $sut?->value);
        self::assertSame($type, $sut->getType());
    }

    #[DataProvider('provideValidAddresses')]
    #[Test]
    public function marshall_returns_value_from_forwarded_id(string $address, IpAddressType $type): void
    {
        $data = [
            'HTTP_TRUE_CLIENT_IP' => "$address, 127.0.0.1",
        ];
        $sut = IpAddress::marshall($data);
        self::assertSame($address, (string)$sut);
        self::assertSame($address, $sut?->value);
        self::assertSame($type, $sut->getType());
    }

    #[DataProvider('provideValidAddresses')]
    #[Test]
    public function marshall_returns_value_from_remote_ip(string $address, IpAddressType $type): void
    {
        $data = [
            'REMOTE_ADDR' => $address,
        ];
        $sut = IpAddress::marshall($data);
        self::assertSame($address, (string)$sut);
        self::assertSame($address, $sut?->value);
        self::assertSame($type, $sut->getType());
    }

    #[Test]
    public function marshall_returns_null_on_sad_path(): void
    {
        $sut = IpAddress::marshall([]);
        self::assertNull($sut);
    }

    #[DataProvider('provideValidAddresses')]
    #[Test]
    public function make_returns_value(string $address, IpAddressType $type): void
    {
        $sut = IpAddress::make($address);
        self::assertSame($address, (string)$sut);
        self::assertSame($address, $sut->value);
        self::assertSame($type, $sut->getType());
    }

    #[DataProvider('provideInvalidAddresses')]
    #[Test]
    public function make_throws_InvalidArgument(string $address): void
    {
        $this->expectException(InvalidArgumentException::class);
        IpAddress::make($address);
    }

    #[DataProvider('provideValidAddresses')]
    #[Test]
    public function tryFrom_returns_value_from_string(string $address, IpAddressType $type): void
    {
        $sut = IpAddress::tryFrom($address);
        self::assertNotNull($sut);
        self::assertSame($address, (string)$sut);
        self::assertSame($address, $sut->value);
        self::assertSame($type, $sut->getType());
    }

    #[DataProvider('provideValidAddresses')]
    #[Test]
    public function tryFrom_returns_value_from_self(string $address): void
    {
        $address = IpAddress::make($address);
        self::assertSame($address, IpAddress::tryFrom($address));
    }

    #[DataProvider('provideValidAddresses')]
    #[Test]
    public function tryFrom_returns_value_from_Stringable(string $address): void
    {
        $value = IpAddress::tryFrom(new readonly class ($address) implements \Stringable {
            public function __construct(private string $address)
            {
            }

            public function __toString(): string
            {
                return $this->address;
            }
        });

        self::assertNotNull($value);
        self::assertSame($address, (string)$value);
    }

    #[DataProvider('provideInvalidAddresses')]
    #[DataProvider('provideNonStringInvalidAddresses')]
    #[Test]
    public function tryFrom_returns_null_when_invalid(mixed $address): void
    {
        self::assertNull(IpAddress::tryFrom($address));
    }

    public static function provideValidAddresses(): Generator
    {
        foreach (
            [
            ['106.112.47.159', IpAddressType::IPv4],
            ['15.146.108.213', IpAddressType::IPv4],
            ['187.73.69.189', IpAddressType::IPv4],
            ['74.240.149.252', IpAddressType::IPv4],
            ['109.247.203.56', IpAddressType::IPv4],
            ['8.165.230.95', IpAddressType::IPv4],
            ['35.104.24.140', IpAddressType::IPv4],
            ['191.178.203.40', IpAddressType::IPv4],
            ['178.245.90.92', IpAddressType::IPv4],
            ['2.52.178.30', IpAddressType::IPv4],
            ['0d65:08ca:fadf:10d3:8a3c:3efa:422d:0df7', IpAddressType::IPv6],
            ['94a9:fbc4:4883:5be2:31d3:642f:b9c3:93dc', IpAddressType::IPv6],
            ['cfe1:bb61:368b:f6a3:251f:502d:5a12:24b9', IpAddressType::IPv6],
            ['fefa:8895:88fc:085b:77b0:7ca8:b096:3680', IpAddressType::IPv6],
            ['4814:d98e:8a2a:791d:2c4d:65f8:659b:6ad5', IpAddressType::IPv6],
            ['68ab:8fbb:8264:3cf4:8a01:0c49:4b30:2b6b', IpAddressType::IPv6],
            ['3e2c:0c16:fce3:d292:0404:3bce:1b6a:f43e', IpAddressType::IPv6],
            ['0f57:1e3f:b0b9:f8b4:4fd2:05ae:baf2:352c', IpAddressType::IPv6],
            ['93b4:ca80:1611:a65b:b643:b9aa:faff:357b', IpAddressType::IPv6],
            ['9a9e:6857:7f05:f213:1391:2093:c1f7:2d92', IpAddressType::IPv6],
            ['::', IpAddressType::IPv6],
            ['::0', IpAddressType::IPv6],
            ] as [$address, $type]
        ) {
            yield $address => [$address, $type];
        }
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
