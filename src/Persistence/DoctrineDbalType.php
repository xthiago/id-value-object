<?php

declare(strict_types=1);

namespace Xthiago\ValueObject\Id\Persistence;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\GuidType;
use Throwable;
use Xthiago\ValueObject\Id\Id;

use Xthiago\ValueObject\Id\IdInterface;
use function is_string;

class DoctrineDbalType extends GuidType
{
    public const NAME = 'xthiago_id';

    /**
     * {@inheritdoc}
     */
    public function convertToPHPValue($value, AbstractPlatform $platform): ?IdInterface
    {
        $valueObjectClass = $this->getConcreteIdClass();

        if ($value instanceof $valueObjectClass) {
            return $value;
        }

        if (is_string($value) === false) {
            return null;
        }

        if ($value === '') {
            return null;
        }

        try {
            $id = $valueObjectClass::fromString($value);
        } catch (Throwable $e) {
            throw ConversionException::conversionFailed($value, self::NAME);
        }

        return $id;
    }

    /**
     * {@inheritdoc}
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if (! $value instanceof IdInterface) {
            return null;
        }

        return $value->__toString();
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return static::NAME;
    }

    /**
     * @psalm-return class-string<IdInterface>
     */
    public function getConcreteIdClass(): string
    {
        return Id::class;
    }

    /**
     * {@inheritdoc}
     */
    public function requiresSQLCommentHint(AbstractPlatform $platform): bool
    {
        return true;
    }
}
