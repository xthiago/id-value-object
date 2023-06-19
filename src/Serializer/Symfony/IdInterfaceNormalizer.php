<?php
declare(strict_types=1);

namespace Xthiago\ValueObject\Id\Serializer\Symfony;

use InvalidArgumentException;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Xthiago\ValueObject\Id\Id;
use Xthiago\ValueObject\Id\IdInterface;

/**
 * @psalm-suppress DeprecatedInterface
 */
class IdInterfaceNormalizer implements NormalizerInterface, DenormalizerInterface, CacheableSupportsMethodInterface
{
    /** @var array<class-string, string|bool|null>  */
    private $supportedClasses = [];

    /** @psalm-param  array<class-string, string|bool|null>  $supportedClasses */
    public function __construct(array $supportedClasses = [])
    {
        $this->supportedClasses = $supportedClasses;
    }

    public static function default(): self
    {
        return new self([
            Id::class => true,
            IdInterface::class => true,
        ]);
    }

    /** {@inheritDoc} */
    public function normalize($object, string $format = null, array $context = [])
    {
        return (string) $object;
    }

    /** {@inheritDoc} */
    public function supportsNormalization($data, string $format = null)
    {
        return $data instanceof IdInterface;
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, string $type, string $format = null, array $context = [])
    {
        try {
            /** @psalm-suppress InvalidStringClass */
            return $type::fromString($data);
        } catch (InvalidArgumentException $exception) {
            throw new NotNormalizableValueException(sprintf(
                'The data is not a valid "%s" string representation.',
                $type
            ));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, string $type, string $format = null)
    {
        return is_a($type, IdInterface::class, true);
    }

    public function getSupportedTypes(?string $format): array
    {
        return $this->supportedClasses;
    }

    /** @deprecated on Symfony 6.3. */
    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }
}
