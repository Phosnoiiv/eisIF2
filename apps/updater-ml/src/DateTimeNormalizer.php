<?php
namespace EverISay\SIF\ML\Updater;

use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer as SymfonyDateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class DateTimeNormalizer implements DenormalizerInterface, CacheableSupportsMethodInterface {
    function __construct() {
        $this->backing = new SymfonyDateTimeNormalizer([
            SymfonyDateTimeNormalizer::TIMEZONE_KEY => '+0000',
        ]);
    }

    private readonly SymfonyDateTimeNormalizer $backing;

    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []) {
        if ('' === $data) return null;
        return $this->backing->denormalize($data, $type, $format, $context);
    }

    public function hasCacheableSupportsMethod(): bool {
        return $this->backing->hasCacheableSupportsMethod();
    }

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null) {
        return $this->backing->supportsDenormalization($data, $type, $format);
    }
}
