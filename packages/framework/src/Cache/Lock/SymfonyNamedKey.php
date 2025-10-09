<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Framework\Cache\Lock;

use PhoneBurner\Pinch\Component\Cache\Lock\NamedKey;
use ReflectionClass;
use Symfony\Component\Lock\Exception\UnserializableKeyException;
use Symfony\Component\Lock\Key;

use function PhoneBurner\Pinch\String\str_prefix;

final readonly class SymfonyNamedKey implements NamedKey
{
    public Key $key;

    public function __construct(public string $name)
    {
        $name || throw new \InvalidArgumentException('The name cannot be empty.');
        $this->key = new Key(str_prefix($name, 'locks.'));
    }

    #[\Override]
    public function __toString(): string
    {
        return 'named_key.' . $this->name;
    }

    public function __serialize(): array
    {
        // This is hacky as anything I've ever written, but igbinary doesn't handle
        // cases where there is custom serialization without an __unserialize() method,
        // and Symfony loves to make everything final, and will change how this works in
        // the next minor versions.
        $r = new ReflectionClass(Key::class);
        if (! $r->getProperty('serializable')->getValue($this->key)) {
            throw new UnserializableKeyException('The key cannot be serialized.');
        }

        return [
            'name' => $this->name,
            'key' => [
                'resource' => $r->getProperty('resource')->getValue($this->key),
                'expiringTime' => $r->getProperty('expiringTime')->getValue($this->key),
                'state' => $r->getProperty('state')->getValue($this->key),
            ],
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->name = $data['name'];
        $this->key = new Key($data['key']['resource']);
        $r = new ReflectionClass(Key::class);
        $r->getProperty('expiringTime')->setValue($this->key, $data['key']['expiringTime']);
        $r->getProperty('state')->setValue($this->key, $data['key']['state']);
    }
}
