<?php

namespace Zenstruck\Document\Bridge\Doctrine\Persistence;

use Zenstruck\Document;
use Zenstruck\Document\LazyDocument;
use Zenstruck\Document\LibraryRegistry;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class ObjectReflector
{
    private \ReflectionObject $ref;

    /** @var array<string,\ReflectionProperty> */
    private array $properties = [];

    public function __construct(private object $object, private array $config)
    {
        $this->ref = new \ReflectionObject($object);
    }

    public function load(LibraryRegistry $registry, ?string $property): void
    {
        if ($property && !isset($this->config[$property])) {
            throw new \InvalidArgumentException(\sprintf('Property "%s" is not configured as a document on "%s".', $property, $this->ref->name));
        }

        foreach ($property ? [$property => $this->config[$property]] : $this->config as $name => $config) {
            $document = $this->get($name);

            if (!$document instanceof LazyDocument) {
                continue;
            }

            $document->setLibrary($registry->get($config['library']));
        }
    }

    public function get(string $property): ?Document
    {
        $ref = $this->property($property);

        if (!$ref->isInitialized($this->object)) {
            return null;
        }

        $document = $ref->getValue($this->object);

        return $document instanceof Document ? $document : null;
    }

    public function set(string $property, Document $document): void
    {
        $this->property($property)->setValue($this->object, $document);
    }

    private function property(string $name): \ReflectionProperty
    {
        // todo embedded

        if (\array_key_exists($name, $this->properties)) {
            return $this->properties[$name];
        }

        $this->properties[$name] = $this->ref->getProperty($name);
        $this->properties[$name]->setAccessible(true);

        return $this->properties[$name];
    }
}
