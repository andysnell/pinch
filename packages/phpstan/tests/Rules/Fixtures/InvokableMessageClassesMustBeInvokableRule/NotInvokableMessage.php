<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Phpstan\Tests\Rules\Fixtures\InvokableMessageClassesMustBeInvokableRule;

use PhoneBurner\Pinch\Component\MessageBus\Message\InvokableMessage;

class NotInvokableMessage implements InvokableMessage
{
    public function make(): self
    {
        return new self();
    }
}
