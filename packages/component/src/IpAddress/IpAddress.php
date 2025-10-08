<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\IpAddress;

use PhoneBurner\Pinch\Attribute\Usage\Contract;
use PhoneBurner\Pinch\Component\IpAddress\Exception\InvalidIpAddress;

#[Contract]
final class IpAddress implements \Stringable
{
    /**
     * True for addresses in a private range, i.e.,
     * IPv4: 10.0.0.0/8, 172.16.0.0/12, 192.168.0.0/16
     * IPv6: addresses starting with "FD" or "FC"
     */
    // phpcs:disable
    public bool $is_private {
        get => \filter_var($this->value, \FILTER_VALIDATE_IP, \FILTER_FLAG_NO_PRIV_RANGE) === false;
    }
    // phpcs:enable

    /**
     * True for addresses in ranges that are "reserved by protocol", i.e.,
     * IPv4: 0.0.0.0/8, 169.254.0.0/16, 127.0.0.0/8, 240.0.0.0/4
     * IPv6: ::1/128, ::/128, ::FFFF:0:0/96, FE80::/10
     */
    // phpcs:disable
    public bool $is_reserved {
        get => \filter_var($this->value, \FILTER_VALIDATE_IP, \FILTER_FLAG_NO_RES_RANGE) === false;
    }
    // phpcs:enable

    public IpAddressType $type;

    public function __construct(public string $value)
    {
        \filter_var($value, \FILTER_VALIDATE_IP) ?: throw new InvalidIpAddress('invalid ip address: ' . $value);
        $this->type = \str_contains($this->value, ':') ? IpAddressType::IPv6 : IpAddressType::IPv4;
    }

    public static function make(string $address): self
    {
        return new self($address);
    }

    public static function tryFrom(mixed $address): self|null
    {
        try {
            return match (true) {
                $address instanceof self => $address,
                \is_string($address) => $address !== '' ? new self($address) : null,
                $address instanceof \Stringable => self::tryFrom((string)$address),
                default => null,
            };
        } catch (InvalidIpAddress) {
            return null;
        }
    }

    #[\Override]
    public function __toString(): string
    {
        return $this->value;
    }

    public function __serialize(): array
    {
        return ['value' => $this->value];
    }

    /**
     * @param array{value: string} $data
     */
    public function __unserialize(array $data): void
    {
        $this->__construct($data['value']);
    }

    /**
     * @param array<string, mixed> $data Often $_SERVER, but could be any array with IP address headers
     */
    public static function marshall(array $data): self|null
    {
        $addresses = $data['HTTP_TRUE_CLIENT_IP']
            ?? $data['HTTP_X_FORWARDED_FOR']
            ?? $data['REMOTE_ADDR']
            ?? null;

        if ($addresses === null) {
            return null;
        }

        \assert(\is_scalar($addresses));

        // use left-most address since the ones to the right are the prox(y|ies).
        $addresses = \explode(',', (string)$addresses);

        return self::tryFrom(\trim(\reset($addresses)));
    }

    public static function local(): self
    {
        return new self(\gethostbyname(\gethostname() ?: 'localhost'));
    }
}
