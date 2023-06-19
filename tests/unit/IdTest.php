<?php

declare(strict_types=1);

namespace Xthiago\ValueObject\Id;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

use Ramsey\Uuid\Uuid;
use function json_encode;
use function sprintf;

class IdTest extends TestCase
{
    /** @dataProvider validIdsEncodedAsString */
    public function test_fromString_should_create_an_vo_instance(string $rawString): void
    {
        $valueObject = Id::fromString($rawString);

        self::assertInstanceOf(Id::class, $valueObject);
        self::assertTrue($valueObject->isEqualTo(Id::fromString($rawString)));
        self::assertSame($rawString, (string) $valueObject);
        self::assertSame($rawString, $valueObject->toString());
        self::assertSame($rawString, $valueObject->jsonSerialize());
    }

    /** @return array<string, string[]> */
    public static function validIdsEncodedAsString(): array
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

    public function test_when_calling_fromString_from_a_child_class_it_must_return_the_subtype_class_instance(): void
    {
        $rawId = 'xablau';

        $superType = Id::fromString($rawId);
        $subTypeA = SubTypeA::fromString($rawId);
        $subTypeB = SubTypeB::fromString($rawId);

        self::assertInstanceOf(Id::class, $superType);
        self::assertInstanceOf(SubTypeA::class, $subTypeA);
        self::assertInstanceOf(SubTypeB::class, $subTypeB);
    }

    public function test_when_calling_generate_from_a_child_class_it_must_return_the_subtype_class_instance(): void
    {
        $superType = Id::generate();
        $subTypeA = SubTypeA::generate();
        $subTypeB = SubTypeB::generate();

        self::assertInstanceOf(Id::class, $superType);
        self::assertInstanceOf(SubTypeA::class, $subTypeA);
        self::assertInstanceOf(SubTypeB::class, $subTypeB);
    }

    public function test_generate_should_create_instance_with_a_unique_ids_and_correct_vo_instance(): void
    {
        $firstId = SubTypeA::generate();
        $secondId = SubTypeA::generate();
        $thirdId = SubTypeA::generate();

        self::assertFalse(
            $firstId->isEqualTo($secondId) && $secondId->isEqualTo($thirdId) && $firstId->isEqualTo($thirdId),
            'Successive calls to Id::generate() should produce different IDs.'
        );
        self::assertNotSame((string) $firstId, (string) $secondId);
        self::assertNotSame((string) $secondId, (string) $thirdId);
        self::assertNotSame((string) $firstId, (string) $thirdId);
    }

    /** @dataProvider differentIdsProviders */
    public function test_isEqualTo_should_return_false(IdInterface $first, IdInterface $second): void
    {
        self::assertFalse($first->isEqualTo($second));
        self::assertFalse($second->isEqualTo($first));
    }

    /** @return array<string, array{first: IdInterface, second: IdInterface}> */
    public static function differentIdsProviders(): array
    {
        return [
            // -- Same class scenarios: ------------------
            'uuid from string' => [
                'first' => Id::fromString('b070993c-c5c7-4cef-b7a1-725e1db13524'),
                'second' => Id::fromString('d840e723-2b9e-4b7a-9964-9150f6dbbe6a'),
            ],
            'numeric string #1' => [
                'first' => Id::fromString('0'),
                'second' => Id::fromString('1'),
            ],
            'numeric string #2' => [
                'first' => Id::fromString('-1'),
                'second' => Id::fromString('1'),
            ],
            'emoji' => [
                'first' => Id::fromString('😁'),
                'second' => Id::fromString('😀'),
            ],
            // -- Different class scenarios: ---------------
            'same value but different class #1' => [
                'first' => Id::fromString('b070993c-c5c7-4cef-b7a1-725e1db13524'),
                'second' => SubTypeA::fromString('b070993c-c5c7-4cef-b7a1-725e1db13524'),
            ],
            'same value but different class #2' => [
                'first' => Id::fromString('0'),
                'second' => SubTypeA::fromString('0'),
            ],
            'same value but different class #3' => [
                'first' => Id::fromString('-1'),
                'second' => SubTypeA::fromString('-1'),
            ],
            'same value but different class #4' => [
                'first' => Id::fromString('😁'),
                'second' => SubTypeA::fromString('😁'),
            ],
        ];
    }

    public function test_jsonSerialize_should_return_the_string_representation(): void
    {
        $id = Id::generate();
        $idEncodedAsString = $id->__toString();

        $resultOfJsonSerializeMethod = $id->jsonSerialize();
        $resultOfjsonEncodeFunction = json_encode($id);

        self::assertSame($idEncodedAsString, $resultOfJsonSerializeMethod);
        self::assertSame(sprintf('"%s"', $idEncodedAsString), $resultOfjsonEncodeFunction);
    }

    public function test_toString_and_jsonSerialize_must_return_same_thing(): void
    {
        $id = Id::generate();

        $toString = $id->toString();
        $__toString = $id->__toString();
        $jsonSerializable = $id->jsonSerialize();

        self::assertSame($toString, $__toString);
        self::assertSame($toString, $jsonSerializable);
        self::assertSame($toString, (string) $id);
    }

    public function test_isEqualTo_can_receive_instances_of_IdInterface_but_always_will_return_false(): void
    {
        $rawId = 'xablau';
        $id = Id::fromString($rawId);
        $anotherId = AnotherIdVo::fromString($rawId);

        self::assertFalse($id->isEqualTo($anotherId));
        self::assertFalse($anotherId->isEqualTo($id));
    }
}

class SubTypeA extends Id {}
class SubTypeB extends Id {}

class AnotherIdVo implements IdInterface
{
    private $id;

    public function __construct(string $id)
    {
    }

    public static function fromString(string $id): IdInterface
    {
        return new static($id);
    }

    public static function generate(): IdInterface
    {
        return new static((string) Uuid::uuid4());
    }

    public function isEqualTo(IdInterface $anotherId): bool
    {
        return static::class === get_class($anotherId) && $this->id === $anotherId->toString();
    }

    public function __toString(): string
    {
        return $this->toString();
    }

    public function toString(): string
    {
        return $this->id;
    }

    public function jsonSerialize(): string
    {
        return $this->toString();
    }
}