<?php

declare(strict_types=1);

namespace Brut\Tests\Unit;

use Brain\Monkey;
use Brain\Monkey\Functions;
use Brut\Utils\ValidatorHelper;
use PHPUnit\Framework\TestCase;

final class ValidatorHelperTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Monkey\setUp();
    }

    protected function tearDown(): void
    {
        Monkey\tearDown();
        parent::tearDown();
    }

    public function testIsValidImageChecksMimeType(): void
    {
        self::assertTrue(ValidatorHelper::isValidImage(['type' => 'image/png']));
        self::assertTrue(ValidatorHelper::isValidImage(['type' => 'image/jpeg']));
        self::assertFalse(ValidatorHelper::isValidImage(['type' => 'application/pdf']));
        self::assertFalse(ValidatorHelper::isValidImage([]));
    }

    public function testIsValidImageHonoursCustomAllowList(): void
    {
        self::assertFalse(ValidatorHelper::isValidImage(['type' => 'image/gif'], ['image/png']));
        self::assertTrue(ValidatorHelper::isValidImage(['type' => 'image/webp'], ['image/webp']));
    }

    public function testIsNotEmptyString(): void
    {
        self::assertTrue(ValidatorHelper::isNotEmptyString(' x '));
        self::assertFalse(ValidatorHelper::isNotEmptyString('   '));
        self::assertFalse(ValidatorHelper::isNotEmptyString(''));
        self::assertFalse(ValidatorHelper::isNotEmptyString(null));
    }

    public function testIsValidId(): void
    {
        self::assertTrue(ValidatorHelper::isValidId(5));
        self::assertTrue(ValidatorHelper::isValidId('5'));
        self::assertFalse(ValidatorHelper::isValidId(0));
        self::assertFalse(ValidatorHelper::isValidId(-1));
        self::assertFalse(ValidatorHelper::isValidId('abc'));
    }

    public function testIsValidEmailDelegatesToWordPress(): void
    {
        Functions\expect('is_email')
            ->once()
            ->with('user@example.com')
            ->andReturn('user@example.com');

        self::assertTrue(ValidatorHelper::isValidEmail('user@example.com'));
    }
}
