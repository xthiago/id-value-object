<?php

declare(strict_types=1);

namespace Xthiago\ValueObject\Id\Serializer\Symfony;

use PHPUnit\Framework\TestCase;
use Xthiago\ValueObject\Id\Id;
use Xthiago\ValueObject\Id\IdInterface;

class IdInterfaceNormalizerTest extends TestCase
{
    /**
     * @var IdInterfaceNormalizer
     */
    private $normalizer;

    protected function setUp(): void
    {
        $this->normalizer = new IdInterfaceNormalizer();
    }

    public function testSupportsNormalization()
    {
        $this->assertTrue($this->normalizer->supportsNormalization(Id::generate()));
        $this->assertTrue($this->normalizer->supportsNormalization(Id::fromString('foo')));
        $this->assertTrue($this->normalizer->supportsNormalization(CustomId::generate()));
        $this->assertTrue($this->normalizer->supportsNormalization(CustomId::fromString('foo')));
        $this->assertFalse($this->normalizer->supportsNormalization([]));
        $this->assertFalse($this->normalizer->supportsNormalization(new \stdClass()));
    }

    public static function normalizeProvider(): iterable
    {
        $data = [
            // uuid v1:
            '8b9e2852-b6b1-4182-ac92-61fe4a3678b5',
            '5cfc244d-b402-4841-86a7-c2b89f8a9046',
            // uuid v4:
            'fc02435e-0e4d-11ee-be56-0242ac120002',
            '015408ba-0e4e-11ee-be56-0242ac120002',
            // others:
            'foo',
            'bar-01',
        ];

        $types = [
            Id::class,
            CustomId::class,
        ];

        foreach ($data as $rawId) {
            foreach ($types as $voType) {
                yield [$rawId, $voType::fromString($rawId)];
            }
        }
    }

    /**
     * @dataProvider normalizeProvider
     */
    public function testNormalize(string $expected, IdInterface $id)
    {
        $this->assertSame($expected, $this->normalizer->normalize($id));
    }

    public static function dataProvider(): array
    {
        return [
            // uuid v1:
            ['8b9e2852-b6b1-4182-ac92-61fe4a3678b5', Id::class],
            ['8b9e2852-b6b1-4182-ac92-61fe4a3678b5', CustomId::class],
            // uuid v4:
            ['fc02435e-0e4d-11ee-be56-0242ac120002', Id::class],
            ['fc02435e-0e4d-11ee-be56-0242ac120002', CustomId::class],
            // others:
            ['bar-01', Id::class],
            ['bar-01', CustomId::class],
        ];
    }

    /**
     * @dataProvider dataProvider
     */
    public function testSupportsDenormalization($idEncodedAsString, $class)
    {
        $this->assertTrue($this->normalizer->supportsDenormalization($idEncodedAsString, $class));
    }

    public function testSupportsDenormalizationForNonId()
    {
        $this->assertFalse($this->normalizer->supportsDenormalization('foo', \stdClass::class));
    }

    /**
     * @dataProvider dataProvider
     */
    public function testDenormalize($idEncodedAsString, $class)
    {
        $this->assertEquals(
            $class::fromString($idEncodedAsString),
            $this->normalizer->denormalize($idEncodedAsString, $class)
        );
    }
}

class CustomId extends Id
{
}
