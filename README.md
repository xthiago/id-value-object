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

## Contributing
Pull requests are welcome. For major changes, please open an issue first to discuss what you would like to change.

Please make sure to update tests as appropriate.

## License
[MIT](LICENSE)