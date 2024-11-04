<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Routing\Command\CommandHelper;

use Generator;
use PhoneBurner\SaltLite\Framework\Routing\Definition\DefinitionList;
use PhoneBurner\SaltLite\Framework\Routing\Definition\RouteDefinition;
use PhoneBurner\SaltLite\Framework\Routing\Route;
use PhoneBurner\SaltLite\Framework\Util\Helper\Arr;
use PhoneBurner\SaltLite\Framework\Util\Helper\Str;
use Symfony\Component\Console\Output\OutputInterface;

abstract class RouteDefinitionListFormatter
{
    protected const string ALL_METHODS = 'ALL';

    protected const array HEADER_FIELDS = [
        'method' => 'METHOD',
        'path' => 'PATH',
        'attributes' => 'ATTRIBUTES',
    ];

    final public function __construct()
    {
    }

    abstract public function render(
        OutputInterface $output,
        DefinitionList $definitions,
        bool $show_attributes = true,
        bool $show_namespaces = true,
    ): int;

    protected static function formatAttributes(
        RouteDefinition $definition,
        bool $show_attributes = true,
        bool $show_namespaces = true,
    ): array {
        if ($show_attributes === false) {
            return [$definition->getAttributes()[Route::class] ?? ''];
        }

        $attributes = [];
        foreach (self::unwrap($definition->getAttributes()) as $name => $attribute) {
            $attributes[] = \vsprintf("%s => %s", [
                $show_namespaces ? $name : Str::shortname($name),
                $show_namespaces ? $attribute : Str::shortname($attribute),
            ]);
        }

        return $attributes;
    }

    /**
     * @param iterable<string,mixed> $attributes
     * @return Generator<string, string>
     */
    protected static function unwrap(iterable $attributes): Generator
    {
        foreach ($attributes as $name => $value) {
            foreach (Arr::wrap($value) as $attribute) {
                yield $name => (string)$attribute;
            }
        }
    }
}
