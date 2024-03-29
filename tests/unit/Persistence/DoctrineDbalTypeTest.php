<?php

declare(strict_types=1);

namespace Xthiago\ValueObject\Id\Persistence;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Xthiago\ValueObject\Id\Id;
use Xthiago\ValueObject\Id\IdInterface;
use Xthiago\ValueObject\Id\Persistence\DoctrineDbalType as IdType;

use function assert;

class DoctrineDbalTypeTest extends TestCase
{
    /** @var AbstractPlatform&MockObject */
    protected $platform;

    /** @var Type  */
    protected $type;

    /** @var string */
    protected $typeName;

    /**
     * @var string
     * @psalm-var class-string<IdInterface>
     */
    protected $valueObjectClass;

    public static function setUpBeforeClass(): void
    {
        Type::addType(IdType::NAME, IdType::class);
    }

    protected function setUp(): void
    {
        $this->platform = $this->aPlatform();
        $this->type = Type::getType(IdType::NAME);
        $this->valueObjectClass = Id::class;
        $this->typeName = IdType::NAME;
    }

    protected function aPlatform(): AbstractPlatform
    {
        $platform = $this->getMockBuilder(AbstractPlatform::class)
            ->addMethods([])
            ->getMockForAbstractClass();

        return $platform;
    }

    public function test_call_convertToDatabaseValue_with_vo_value_should_return_id_encoded_as_string(): void
    {
        $phpValue = $this->valueObjectClass::fromString('1');

        $databaseValue = $this->type->convertToDatabaseValue($phpValue, $this->platform);

        self::assertSame('1', $databaseValue);
    }

    public function test_call_convertToDatabaseValue_with_null_value_should_return_null(): void
    {
        $phpValue = null;
        $databaseValue = $this->type->convertToDatabaseValue($phpValue, $this->platform);

        self::assertNull($databaseValue);
    }

    public function test_call_convertToPHPValue_with_a_valid_id_encoded_as_string_must_return_a_vo(): void
    {
        $databaseValue = '11';

        $phpValue = $this->type->convertToPHPValue($databaseValue, $this->platform);

        self::assertInstanceOf($this->valueObjectClass, $phpValue);
        self::assertSame('11', (string) $phpValue);
    }

    public function test_call_convertToPHPValue_with_a_null_value_must_return_null(): void
    {
        $databaseValue = null;

        $phpValue = $this->type->convertToPHPValue($databaseValue, $this->platform);

        self::assertNull($phpValue);
    }

    public function test_call_convertToPHPValue_with_a_vo_instance_as_value_must_return_the_given_vo(): void
    {
        $databaseValue = $this->valueObjectClass::fromString('11');

        $phpValue = $this->type->convertToPHPValue($databaseValue, $this->platform);

        self::assertEquals($this->valueObjectClass::fromString('11'), $phpValue);
    }

    public function test_should_implement_some_methods_to_allow_structure_changes_to_be_detected_correctly(): void
    {
        self::assertSame($this->typeName, $this->type->getName());
        self::assertTrue($this->type->requiresSQLCommentHint($this->platform));
    }
}
