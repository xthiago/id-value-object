<?php

declare(strict_types=1);

namespace Xthiago\ValueObject\Id;

use InvalidArgumentException;
use JsonSerializable;

/**
 * @template T
 * @psalm-immutable
 */
interface IdInterface extends JsonSerializable
{
    /**
     * Creates a new IdInterface instance from string.
     *
     * @psalm-param non-empty-string $id
     * @psalm-return IdInterface<T>
     * @throws InvalidArgumentException if not able to create a valid Id from string.
     */
    public static function fromString(string $id): self;

    /**
     * Generates a new unique IdInterface instance.
     *
     * @psalm-return IdInterface<T>
     */
    public static function generate(): self;

    /**
     * Compare two instances of IdInterface.
     *
     * It compares the values and the class instances. If both objects have the same value but are different classes,
     * they will be considered not equal.
     *
     * @psalm-param IdInterface<T> $anotherId
     */
    public function isEqualTo(IdInterface $anotherId): bool;

    /**
     * Returns the string representation of the IdInterface.
     * @psalm-return non-empty-string
     */
    public function __toString(): string;

    /**
     * Returns the string representation of the IdInterface.
     * @psalm-return non-empty-string
     */
    public function toString(): string;
}
