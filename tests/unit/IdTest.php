<?php

declare(strict_types=1);

namespace Xthiago\ValueObject\Id;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class IdTest extends TestCase
{
    /** @dataProvider validIdsEncodedAsString */
    public function test_fromString_should_create_an_instance_from_string(string $rawString): void
    {
        $valueObject = Id::fromString($rawString);

        self::assertTrue($valueObject->isEqualTo(Id::fromString($rawString)));
        self::assertSame($rawString, (string) $valueObject);
    }

    /** @return array<string, string[]> */
    public function validIdsEncodedAsString(): array
    {
        return [
            'uuid version 1' => ['rawString' => '18f3b588-bbe4-11eb-8529-0242ac130003'],
            'uuid version 4' => ['rawString' => 'bf01abdd-34f1-4a60-b263-00a73499eae0'],
            'a zero string' => ['rawString' => '0'],
            'a positive integer encoded as string' => ['rawString' => '83123123321'],
            'a negative integer encoded as string' => ['rawString' => '-83123123321'],
            'a float number encoded as string' => ['rawString' => '3.14159265359'],
            'an emoji' => ['rawString' => '🤙'],
            'a word' => ['rawString' => 'Lorem'],
            'a sentence' => ['rawString' => 'Lorem ipsum dolor sit amet'],
            'a hash symbol' => ['rawString' => '#'],
            'a string' => ['rawString' => 'user-7310'],
        ];
    }

    public function test_fromString_should_throw_exception_when_empty_string_are_provided(): void
    {
        self::expectException(InvalidArgumentException::class);

        Id::fromString('');
    }

    public function test_generate_should_create_instance_with_a_unique_id(): void
    {
        $firstId = Id::generate();
        $secondId = Id::generate();
        $thirdId = Id::generate();

        self::assertFalse(
            $firstId->isEqualTo($secondId) && $secondId->isEqualTo($thirdId) && $firstId->isEqualTo($thirdId),
            'Successive calls to Id::generate() should produce different IDs.'
        );
        self::assertNotSame((string) $firstId, (string) $secondId);
        self::assertNotSame((string) $secondId, (string) $thirdId);
        self::assertNotSame((string) $firstId, (string) $thirdId);
    }

    /** @dataProvider differentIdsProviders */
    public function test_isEqualTo_should(string $first, string $second): void
    {
        $firstId = Id::fromString($first);
        $secondId = Id::fromString($second);

        self::assertFalse($firstId->isEqualTo($secondId));
    }

    /** @return array<string, string[]> */
    public function differentIdsProviders(): array
    {
        return [
            ['0', '1'],
            ['b070993c-c5c7-4cef-b7a1-725e1db13524', 'd840e723-2b9e-4b7a-9964-9150f6dbbe6a'],
            ['-1', '1'],
            ['😁', '😀'],
        ];
    }
}
