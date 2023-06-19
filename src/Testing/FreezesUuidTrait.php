<?php

declare(strict_types=1);

namespace Xthiago\ValueObject\Id\Testing;

use ArrayIterator;
use Iterator;
use Ramsey\Uuid\Uuid;
use \Ramsey\Uuid\UuidInterface;
use Ramsey\Uuid\UuidFactory;
use RuntimeException;

trait FreezesUuidTrait
{
    private function freezeUuidV4WithFixedValue(string $uuid): void
    {
        $factory = new class($uuid) extends UuidFactory {
            /** @var string */
            private $uuid;

            public function __construct(string $uuid)
            {
                $this->uuid = $uuid;
                parent::__construct();
            }

            public function uuid4(): UuidInterface
            {
                return Uuid::fromString($this->uuid);
            }
        };

        Uuid::setFactory($factory);
    }

    private function freezeUuidV4WithKnownSequence(string ...$uuids): void
    {
        $sequence = new ArrayIterator($uuids);
        $factory = new class($sequence) extends UuidFactory {
            /** @var Iterator */
            private $uuidSequence;

            public function __construct(Iterator $uuidSequence)
            {
                $this->uuidSequence = $uuidSequence;
                parent::__construct();
            }

            public function uuid4(): UuidInterface
            {
                if (false === $this->uuidSequence->valid()) {
                    throw new RuntimeException(
                        'The UUID sequence are over. Maybe more UUIDs were used than initially configured.'
                    );
                }
                $uuid = $this->uuidSequence->current();
                $this->uuidSequence->next();

                return Uuid::fromString($uuid);
            }
        };

        Uuid::setFactory($factory);
    }

    private function unfreezeUuid(): void
    {
        Uuid::setFactory(new UuidFactory());
    }
}
