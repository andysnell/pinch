<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Phpstan\Tests\Rules\Fixtures\InvokableMessageClassesMustBeInvokableRule;

use PhoneBurner\Pinch\Component\MessageBus\Message\InvokableMessage;

class ActuallyInvokableMessage implements InvokableMessage
{
    public function __invoke(): self
    {
        return new self();
    }
}
