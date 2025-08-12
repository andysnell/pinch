<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Http\Message;

use Laminas\Diactoros\Response\Serializer;
use PhoneBurner\Pinch\Component\Http\Stream\TemporaryStream;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

/**
 * @implements MessageSerializer<ResponseInterface>
 */
class ResponseSerializer implements MessageSerializer
{
    public function serialize(MessageInterface $message): string
    {
        if (! $message instanceof ResponseInterface) {
            throw new \InvalidArgumentException('Message must be an instance of ResponseInterface');
        }

        return Serializer::toString($message);
    }

    public function deserialize(\Stringable|StreamInterface|string $message): ResponseInterface
    {
        if (! $message instanceof StreamInterface) {
            $message = new TemporaryStream((string)$message);
        }

        return Serializer::fromStream($message);
    }
}
