<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Util\Crypto;

final readonly class IntegerId
{
    public const int ID_MIN = 0;

    public const int ID_MAX = \PHP_INT_MAX;

    public const int NAMESPACE_MIN = 0;

    public const int NAMESPACE_MAX = 2 ** 32 - 1;

    private const string ERROR_TEMPLATE = '%s Must Be an Integer Between %s and %s, inclusive. Got %s';

    /**
     * @var int<0,max>
     */
    public int $value;

    /**
     * @var int<0,4294967295>
     */
    public int $namespace;

    /**
     * @phpstan-assert int<0,max> $value
     * @phpstan-assert int<0,4294967295> $namespace
     */
    private function __construct(int $value, int $namespace)
    {
        if ($value < self::ID_MIN) {
            throw new \InvalidArgumentException(\vsprintf(self::ERROR_TEMPLATE, [
                '$value',
                self::ID_MIN,
                self::ID_MAX,
                $value,
            ]));
        }
        $this->value = $value;

        if ($namespace < self::NAMESPACE_MIN || $namespace > self::NAMESPACE_MAX) {
            throw new \InvalidArgumentException(\vsprintf(self::ERROR_TEMPLATE, [
                '$namespace',
                self::NAMESPACE_MIN,
                self::NAMESPACE_MAX,
                $namespace,
            ]));
        }
        $this->namespace = $namespace;
    }

    public static function make(int $value, int $namespace = self::ID_MIN): self
    {
        return new self($value, $namespace);
    }
}
