<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Util;

/**
 * Immutable, self-validating wrapper object for class-string values.
 * Supports enums, classes, and interfaces, but not traits.
 *
 * @template T of object
 */
final readonly class ClassString implements \Stringable
{
    /**
     * @var class-string<T>
     */
    public string $value;

    public ClassStringType $type;

    /**
     * @phpstan-assert ClassString<T> $this
     */
    public function __construct(string $value)
    {
        $this->type = match (true) {
            \enum_exists($value) => ClassStringType::Enum,
            \class_exists($value) => ClassStringType::Object,
            \interface_exists($value) => ClassStringType::Interface,
            \trait_exists($value) => throw new \UnexpectedValueException("Traits are not supported"),
            default => throw new \UnexpectedValueException("Class $value does not exist"),
        };

        /**
         * @var class-string<T> $value
         */
        $this->value = $value;
    }

    /**
     * @param self|object|class-string $class
     */
    public function is(object|string $class): bool
    {
        if (\is_object($class)) {
            $class = $class instanceof self ? $class->value : $class::class;
        }

        return \is_a($this->value, $class, true);
    }

    /**
     * @return \ReflectionClass<T>
     */
    public function reflect(): \ReflectionClass
    {
        return new \ReflectionClass($this->value);
    }

    /**
     * @return class-string<T>
     */
    public function __toString(): string
    {
        return $this->value;
    }

    public function __serialize(): array
    {
        return [$this->value];
    }

    public function __unserialize(array $data): void
    {
        $this->__construct($data[0]);
    }
}
