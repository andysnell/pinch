<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Phpstan\Tests\Rules\Architecture;

use PhoneBurner\Pinch\Phpstan\Rules\Architecture\DependencyHierarchyRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<DependencyHierarchyRule>
 */
final class DependencyHierarchyRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new DependencyHierarchyRule();
    }

    public function testCoreCannotDependOnComponent(): void
    {
        $this->analyse(
            [__DIR__ . '/Fixtures/core-depends-on-component.php'],
            [
                [
                    'Core package class PhoneBurner\Pinch\String\TestClass imports component package class PhoneBurner\Pinch\Component\Cryptography\Asymmetric\SignatureKeyPair. This violates the dependency hierarchy (core → component → framework).',
                    8,
                ],
                [
                    'Core package class PhoneBurner\Pinch\String\TestClass imports component package class PhoneBurner\Pinch\Component\EmailAddress\EmailAddress. This violates the dependency hierarchy (core → component → framework).',
                    9,
                ],
                [
                    'Core package class PhoneBurner\Pinch\String\TestClass calls static method on component package class PhoneBurner\Pinch\Component\Cryptography\Asymmetric\SignatureKeyPair. This violates the dependency hierarchy (core → component → framework).',
                    16,
                ],
                [
                    'Core package class PhoneBurner\Pinch\String\TestClass instantiates component package class PhoneBurner\Pinch\Component\EmailAddress\EmailAddress. This violates the dependency hierarchy (core → component → framework).',
                    17,
                ],
            ],
        );
    }

    public function testComponentCannotDependOnFramework(): void
    {
        $this->analyse(
            [__DIR__ . '/Fixtures/component-depends-on-framework.php'],
            [
            [
                'Component package class PhoneBurner\Pinch\Component\Http\TestClass imports framework package class PhoneBurner\Pinch\Framework\Http\HttpServiceProvider. This violates the dependency hierarchy (core → component → framework).',
                8,
            ],
            [
                'Component package class PhoneBurner\Pinch\Component\Http\TestClass instantiates framework package class PhoneBurner\Pinch\Framework\Http\HttpServiceProvider. This violates the dependency hierarchy (core → component → framework).',
                15,
            ],
            ],
        );
    }

    public function testValidDependencies(): void
    {
        $this->analyse([__DIR__ . '/Fixtures/valid-dependencies.php'], []);
    }
}
