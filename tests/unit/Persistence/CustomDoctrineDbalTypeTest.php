<?php

declare(strict_types=1);

namespace Xthiago\ValueObject\Id\Persistence;

use Doctrine\DBAL\Types\Type;
use Xthiago\ValueObject\Id\Id;

class CustomDoctrineDbalTypeTest extends DoctrineDbalTypeTest
{
    public static function setUpBeforeClass(): void
    {
        Type::addType(ProductIdDbalType::NAME, ProductIdDbalType::class);
    }

    protected function setUp(): void
    {
        $this->platform = $this->aPlatform();
        $this->type = Type::getType(ProductIdDbalType::NAME);
        $this->valueObjectClass = ProductId::class;
        $this->typeName = ProductIdDbalType::NAME;
    }
}

class ProductId extends Id {}

class ProductIdDbalType extends DoctrineDbalType
{
    public const NAME = 'product_id';

    public function getConcreteIdClass(): string
    {
        return ProductId::class;
    }
}
