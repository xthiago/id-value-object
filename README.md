# Id Value Object

PHP library to make working with object identity (ID) easier and fun!

In short: You shouldn't rely on database mechanisms to generate IDs. Also, you shouldn't manipulate scalar values (usual int) over all the application. Wherever you need to generate an ID, use this
ID value object.

## Features

### UUID v4

The library automatically generates UUID v4 as ID. You will not rely on persistence mechanism and flush operations to generate identity.

### Integration with Doctrine

The library comes with a Doctrine DBAL Type which allows you to map the Id value object as an Doctrine Entity attribute.

## Requirements

This library requires PHP >= 7.2.

## Installation

Use [composer](https://getcomposer.org/) to install the library.

```bash
composer install xthiago/id-value-object
```

### Doctrine

To make use of Doctrine integration for persistence you have to configure the Doctrine DBAL Type given by this package.

#### Standalone Doctrine

You must register the DBAL Type in your application boostrap like following:

```php
<?php

\Doctrine\DBAL\Types\Type::addType(
    'xthiago_id', 
    \Xthiago\ValueObject\Id\Persistence\DoctrineDbalType::class
);
```

#### Symfony Framework

If you are using Symfony, you just need to edit the following the Doctrine configuration to add the following:

```yaml
doctrine:
  dbal:
    types:
      xthiago_id: Xthiago\ValueObject\Id\Persistence\DoctrineDbalType
```

## Usage

Basic examples:

```php
<?php
namespace YourApp;

use Xthiago\ValueObject\Id\Id;

// Generate a new ID (UUID v4):
$generatedId = Id::generate();
echo $generatedId; // prints something like `b18c7bbe-da70-4c86-8b8f-145abb21a7c7`.

// Create an ID from string:
$parsedId = Id::fromString('Foo');
echo $parsedId; // prints 'Foo'.

// Comparing two instances of Id:
var_dump($generatedId->isEqualTo($parsedId)); // prints: `false`
var_dump($parsedId->isEqualTo(Id::fromString('Foo'))); // prints: `true`   
````

Mapping Id in your entity class (e.g. Product):

```php
<?php
namespace YourApp;

use Doctrine\ORM\Mapping as ORM;
use Xthiago\ValueObject\Id\Id;

/**
 * @ORM\Entity()
 */
class Product
{
    /**
     * @ORM\Id
     * @ORM\Column(type="xthiago_id", name="id")
     *
     * @var Id 
     * @psalm-var Id<Product>   
     */
    private $id;
    
    // other attributes goes here.
    
    public function __construct(Id $id) 
    {
        $this->id = $id;
    }
    
    /** @psalm-return Id<Product> */
    public function id(): Id
    {
        return $this->id();
    }
}
```
### Creating custom ID classes

Instead of rely on a generic `Id` class for all entities, you can create specific value objects for each entity.

For example, give you have a `Product` entity, you can create a `ProductId` value object in the following way:

```php
class ProductId extends Id 
{
}
```

Then you will need to map this new type. You can just extend `DoctrineDbalType` in the following way:

```php
class ProductIdDbalType extends DoctrineDbalType
{
    public const NAME = 'product_id';

    public function getConcreteIdClass(): string
    {
        return ProductId::class;
    }
}
```

Then you configure this new type (e.g. `Type::addType(ProductIdDbalType::NAME, ProductIdDbalType::class);`) and start
using it in your model (this time I will show the mapping using PHP Attributes):

```php
<?php
namespace YourApp;

use Doctrine\ORM\Mapping as ORM;

#[
    ORM\Entity,
    ORM\Table('product')
]
class Product
{
    public function __construct(
        #[
            ORM\Column(name: 'id', type: ProductIdDbalType::NAME),
            ORM\Id
        ]
        private ProductId $id,
    ) 
    {}
}
```

### Symfony Serializer

This library includes a class [`IdInterfaceNormalizer`](src/Serializer/Symfony/IdInterfaceNormalizer.php) able to 
normalize and denormalize instances of `Id` value object.

To use that normalizer, you need to pass it as an argument of `Serializer` class constructor:

```php
$serializer = new Serializer(
    normalizers: [
        new IdInterfaceNormalizer(), // <---
        // others normalizers...
    ],
    encoders: [new JsonEncoder()]
);
```

### `FreezesUuidTrait` utility to help with tests

When creating tests, we may want to know the value of the generated IDs beforehand. This lets us write more simpler 
assertions. This library provides a trait [`FreezesUuidTrait`](src/Testing/FreezesUuidTrait.php) that could be used
to freeze the uuid v4 generation or to set a known sequence.  

```php
class MyAwesomeTest extends Testcase
{
    use FreezesUuidTrait;
    
    protected function setUp(): void
    {
        // we could freeze the uuids here :)
    }

    protected function tearDown(): void
    {
        $this->unfreezeUuid(); // <-- This is important to unfreeze the uuid generation.
    }
    
    public function test_fixed_uuid(): void
    {
        // Fixing the uuid value (this can also be set on `setUp()` method):
        $this->freezeUuidV4WithFixedValue('866cc948-b6de-4cdc-8f5e-3b53a58a9f63');
        
        // All generated Id will have the same uuid value.
        $this->assertSame('866cc948-b6de-4cdc-8f5e-3b53a58a9f63', (string) Id::generate());
        $this->assertSame('866cc948-b6de-4cdc-8f5e-3b53a58a9f63', (string) Id::generate());
        $this->assertSame('866cc948-b6de-4cdc-8f5e-3b53a58a9f63', (string) Id::generate());
    }
    
    public function test_fixed_uuid_sequence(): void
    {
        // Fixing the uuid value with a known sequence:
        $this->freezeUuidV4WithKnownSequence(
            'e2d5d0fd-a719-4da7-976d-b5cd184fa615'
            '4c98d212-19ed-4c41-9ea2-4b2d48d7410d'
            '2acfaf05-25c3-4db9-9fea-5d71a0c3f909'
        );
        
        // Each new Id instance will assume one uuid from the known sequence:
        $this->assertSame('e2d5d0fd-a719-4da7-976d-b5cd184fa615', (string) Id::generate());
        $this->assertSame('4c98d212-19ed-4c41-9ea2-4b2d48d7410d', (string) Id::generate());
        $this->assertSame('2acfaf05-25c3-4db9-9fea-5d71a0c3f909', (string) Id::generate());
        
        // If we call again Id::generate(), it will throw RuntimeException because there is no remaining uuid in the 
        // available list.
        $this->assertException(RunTimeException::class);
        Id::generate();
        $this->fail('The expected exception was not thrown.');
    }    
}
```

## Contributing
Pull requests are welcome. For major changes, please open an issue first to discuss what you would like to change.

Please make sure to update tests as appropriate.

## License
[MIT](LICENSE)