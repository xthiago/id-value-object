<?php

declare(strict_types=1);

namespace Xthiago\Id;

use InvalidArgumentException;
use Ramsey\Uuid\Uuid;

use function strlen;

/**
 * @template T
 * @psalm-immutable
 */
final class Id
{
    /** @var string */
    private $id;

    private function __construct(string $id)
    {
        if (strlen($id) === 0) {
            throw new InvalidArgumentException('The given id should not be empty.');
        }

        $this->id = $id;
    }

    /**
     * @psalm-return Id<T>
     */
    public static function fromString(string $id): self
    {
        return new self($id);
    }

    /**
     * @psalm-return Id<T>
     */
    public static function generate(): self
    {
        $id = Uuid::uuid4();

        return new self($id->toString());
    }

    public function __toString(): string
    {
        return $this->id;
    }

    /**
     * @psalm-param Id<T> $id
     */
    public function isEqualTo(Id $id): bool
    {
        return $this->id === $id->id;
    }
}
