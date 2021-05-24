<?php

declare(strict_types=1);

namespace Xthiago\Id\Persistence;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\StringType;
use Xthiago\Id\Id;

use function is_string;
use function strlen;

class DoctrineDbalType extends StringType
{
    public const NAME = 'xthiago_id';

    /**
     * {@inheritdoc}
     */
    public function convertToPHPValue($value, AbstractPlatform $platform): ?Id
    {
        if (is_string($value) === false || strlen($value) === 0) {
            return null;
        }

        return Id::fromString($value);
    }

    /**
     * {@inheritdoc}
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if (! $value instanceof Id) {
            return null;
        }

        return $value->__toString();
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function requiresSQLCommentHint(AbstractPlatform $platform)
    {
        return true;
    }
}
