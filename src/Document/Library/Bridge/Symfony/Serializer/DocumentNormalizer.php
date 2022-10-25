<?php

namespace Zenstruck\Document\Library\Bridge\Symfony\Serializer;

use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Zenstruck\Document;
use Zenstruck\Document\LazyDocument;
use Zenstruck\Document\LibraryRegistry;
use Zenstruck\Document\SerializableDocument;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class DocumentNormalizer implements NormalizerInterface, DenormalizerInterface, CacheableSupportsMethodInterface
{
    public const LIBRARY = 'library';
    public const METADATA = 'metadata';

    public function __construct(private LibraryRegistry $registry)
    {
    }

    /**
     * @param Document $object
     */
    final public function normalize(mixed $object, ?string $format = null, array $context = []): string|array
    {
        if ($metadata = $context[self::METADATA] ?? null) {
            return (new SerializableDocument($object, $metadata))->serialize();
        }

        return $object->path();
    }

    final public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof Document;
    }

    /**
     * @param string $data
     */
    final public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): Document
    {
        $document = new LazyDocument($data);

        if ($library = $context[self::LIBRARY] ?? null) {
            $document->setLibrary($this->registry()->get($library));
        }

        return $document;
    }

    final public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        return Document::class === $type;
    }

    final public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }

    protected function registry(): LibraryRegistry
    {
        return $this->registry;
    }
}
