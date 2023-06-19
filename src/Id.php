<?php

declare(strict_types=1);

namespace Xthiago\ValueObject\Id;

use InvalidArgumentException;
use Ramsey\Uuid\Uuid;

use function strlen;

/**
 * @template T
 * @template-implements IdInterface<T>
 * @psalm-immutable
 */
class Id implements IdInterface
{
    /**
     * @var string
     * @psalm-var non-empty-string
     */
    private $id;

    /** @psalm-param  non-empty-string $id */
    final private function __construct(string $id)
    {
        /** @psalm-suppress DocblockTypeContradiction Because it's a library and the clients may don't have psalm. */
        if (strlen($id) === 0) {
            throw new InvalidArgumentException('The given id should not be empty.');
        }

        $this->id = $id;
    }

    /**
     * @psalm-param non-empty-string $id
     * @psalm-return Id<T>
     */
    public static function fromString(string $id): IdInterface
    {
        return new static($id);
    }

    /** @psalm-return Id<T> */
    public static function generate(): IdInterface
    {
        $id = Uuid::uuid4();

        return new static($id->toString());
    }

    public function isEqualTo(IdInterface $anotherId): bool
    {
        if (static::class !== get_class($anotherId)) {
            return false;
        }

        return $this->id === $anotherId->id;
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
