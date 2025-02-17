<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Tests\Util\Helper\Fixture;

use Generator;
use Iterator;
use stdClass;
use Traversable;

class PropertyFixture
{
    public $public_property; // @phpstan-ignore-line
    protected $protected_property; // @phpstan-ignore-line
    private $private_property; // @phpstan-ignore-line

    private $not_typed; // @phpstan-ignore-line
    private readonly string $string_property; // @phpstan-ignore-line
    private readonly int $int_property; // @phpstan-ignore-line
    private readonly float $float_property; // @phpstan-ignore-line
    private readonly bool $bool_property; // @phpstan-ignore-line
    private readonly array $array_property; // @phpstan-ignore-line
    private readonly iterable $iterable_property; // @phpstan-ignore-line
    private readonly Iterator $iterator_property; // @phpstan-ignore-line
    private readonly Generator $generator_property; // @phpstan-ignore-line
    private readonly Traversable $traversable_property; // @phpstan-ignore-line
    private readonly stdClass $class_property; // @phpstan-ignore-line
    private readonly ReflectsLightWaves $interface_property; // @phpstan-ignore-line
    private readonly Mirror $concrete_property; // @phpstan-ignore-line
    private readonly self $self_property; // @phpstan-ignore-line
    private readonly self $class_self_property; // @phpstan-ignore-line

    private string|null $nullable_string_property = null; // @phpstan-ignore-line
    private int|null $nullable_int_property = null; // @phpstan-ignore-line
    private float|null $nullable_float_property = null; // @phpstan-ignore-line
    private bool|null $nullable_bool_property = null; // @phpstan-ignore-line
    private array|null $nullable_array_property = null; // @phpstan-ignore-line
    // phpcs:disable SlevomatCodingStandard.TypeHints.DNFTypeHintFormat
    private ?iterable $nullable_iterable_property = null; // @phpstan-ignore-line
    // phpcs:enable
    private Iterator|null $nullable_iterator_property = null; // @phpstan-ignore-line
    private Generator|null $nullable_generator_property = null; // @phpstan-ignore-line
    private Traversable|null $nullable_traversable_property = null; // @phpstan-ignore-line
    private stdClass|null $nullable_class_property = null; // @phpstan-ignore-line
    private ReflectsLightWaves|null $nullable_interface_property = null; // @phpstan-ignore-line
    private Mirror|null $nullable_concrete_property = null; // @phpstan-ignore-line
    private self|null $nullable_self_property = null; // @phpstan-ignore-line
    private self|null $nullable_class_self_property = null; // @phpstan-ignore-line
}
