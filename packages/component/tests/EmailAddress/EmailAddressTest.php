<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Tests\EmailAddress;

use PhoneBurner\Pinch\Component\EmailAddress\EmailAddress;
use PhoneBurner\Pinch\Component\EmailAddress\Exception\InvalidEmailAddress;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

final class EmailAddressTest extends TestCase
{
    private const string VALID_EMAIL = 'test@phoneburner.com';

    private const string VALID_NAME = 'John Doe';

    private const string VALID_FULL = 'John Doe <test@phoneburner.com>';

    #[Test]
    public function itCanBeInstantiatedWithJustAddress(): void
    {
        $email = new EmailAddress(self::VALID_EMAIL);
        self::assertInstanceOf(EmailAddress::class, $email);

        self::assertSame($email, $email->getEmailAddress());
        self::assertSame(self::VALID_EMAIL, $email->address);
        self::assertSame('', $email->name);
        self::assertSame(self::VALID_EMAIL, (string)$email);
        self::assertSame(self::VALID_EMAIL, $email->jsonSerialize());

        $serialized = \serialize($email);
        self::assertEquals($email, \unserialize($serialized));
    }

    #[Test]
    public function itCanBeInstantiatedWithAddressAndName(): void
    {
        $email = new EmailAddress(self::VALID_EMAIL, self::VALID_NAME);
        self::assertInstanceOf(EmailAddress::class, $email);

        self::assertSame($email, $email->getEmailAddress());
        self::assertSame(self::VALID_EMAIL, $email->address);
        self::assertSame(self::VALID_NAME, $email->name);
        self::assertSame(self::VALID_FULL, (string)$email);
        self::assertSame(self::VALID_FULL, $email->jsonSerialize());

        $serialized = \serialize($email);
        self::assertEquals($email, \unserialize($serialized));
    }

    #[Test]
    public function instanceReturnsEmailAddressFromAddressAlone(): void
    {
        $email = EmailAddress::instance(self::VALID_EMAIL);
        self::assertSame(self::VALID_EMAIL, $email->address);
        self::assertSame('', $email->name);
        self::assertSame(self::VALID_EMAIL, (string)$email);
        self::assertSame(self::VALID_EMAIL, $email->jsonSerialize());

        $serialized = \serialize($email);
        self::assertEquals($email, \unserialize($serialized));
    }

    #[Test]
    public function instanceReturnsEmailAddressFromFullAddress(): void
    {
        $email = EmailAddress::instance(self::VALID_FULL);
        self::assertSame(self::VALID_EMAIL, $email->address);
        self::assertSame(self::VALID_NAME, $email->name);
        self::assertSame(self::VALID_FULL, (string)$email);
        self::assertSame(self::VALID_FULL, $email->jsonSerialize());

        $serialized = \serialize($email);
        self::assertEquals($email, \unserialize($serialized));
    }

    #[Test]
    public function instanceReturnsSelf(): void
    {
        $email = new EmailAddress(self::VALID_EMAIL, self::VALID_NAME);
        self::assertSame($email, EmailAddress::instance($email));
    }

    #[Test]
    public function parseReturnsEmailAddressFromAddressAlone(): void
    {
        $email = EmailAddress::parse(self::VALID_EMAIL);
        self::assertInstanceOf(EmailAddress::class, $email);
        self::assertSame(self::VALID_EMAIL, $email->address);
        self::assertSame('', $email->name);
        self::assertSame(self::VALID_EMAIL, (string)$email);
        self::assertSame(self::VALID_EMAIL, $email->jsonSerialize());

        $serialized = \serialize($email);
        self::assertEquals($email, \unserialize($serialized));
    }

    #[Test]
    public function parseReturnsEmailAddressFromFullAddress(): void
    {
        $email = EmailAddress::parse(self::VALID_FULL);
        self::assertInstanceOf(EmailAddress::class, $email);
        self::assertSame(self::VALID_EMAIL, $email->address);
        self::assertSame(self::VALID_NAME, $email->name);
        self::assertSame(self::VALID_FULL, (string)$email);
        self::assertSame(self::VALID_FULL, $email->jsonSerialize());

        $serialized = \serialize($email);
        self::assertEquals($email, \unserialize($serialized));
    }

    #[Test]
    public function parseReturnsSelf(): void
    {
        $email = new EmailAddress(self::VALID_EMAIL, self::VALID_NAME);
        self::assertSame($email, EmailAddress::parse($email));
    }

    #[TestWith([''])]
    #[TestWith(['john'])]
    #[TestWith(['john@'])]
    #[TestWith(['john@phoneburner'])]
    #[Test]
    public function invalidEmailResultsInException(string $invalid_email): void
    {
        $this->expectException(InvalidEmailAddress::class);

        new EmailAddress($invalid_email);
    }

    #[TestWith([null])]
    #[TestWith([''])]
    #[TestWith(['john'])]
    #[TestWith(['john@'])]
    #[TestWith(['john@phoneburner'])]
    #[Test]
    public function parseInvalidEmailResultsInNull(mixed $invalid_email): void
    {
        self::assertNull(EmailAddress::parse($invalid_email));
    }
}
