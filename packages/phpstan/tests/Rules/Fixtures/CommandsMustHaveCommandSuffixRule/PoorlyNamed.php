<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Phpstan\Tests\Rules\Fixtures\CommandsMustHaveCommandSuffixRule;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;

#[AsCommand('foo:bar', description: 'A poorly named command')]
class PoorlyNamed extends Command
{
}
