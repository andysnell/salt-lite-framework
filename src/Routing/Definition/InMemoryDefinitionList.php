<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Routing\Definition;

use Generator;
use PhoneBurner\SaltLite\Framework\Routing\Route;
use UnexpectedValueException;

/**
 * @implements \IteratorAggregate<RouteDefinition>
 */
class InMemoryDefinitionList implements DefinitionList, \IteratorAggregate
{
    /**
     * @var Definition[]
     */
    private array $definitions;

    /**
     * @var RouteDefinition[]
     */
    private array $named = [];

    public static function make(Definition ...$definitions): self
    {
        return new self(...$definitions);
    }

    private function __construct(Definition ...$definitions)
    {
        $this->definitions = $definitions;

        foreach ($this as $definition) {
            if ($name = $definition->getAttributes()[Route::class] ?? null) {
                $this->named[$name] = $definition;
            }
        }
    }

    #[\Override]
    public function getNamedRoute(string $name): RouteDefinition
    {
        if (! $this->hasNamedRoute($name)) {
            throw new \LogicException('invalid name: ' . $name);
        }

        return $this->named[$name];
    }

    #[\Override]
    public function hasNamedRoute(string $name): bool
    {
        return isset($this->named[$name]);
    }

    /**
     * @return Generator<RouteDefinition>
     */
    #[\Override]
    public function getIterator(): Generator
    {
        foreach ($this->definitions as $definition) {
            if ($definition instanceof RouteGroupDefinition) {
                yield from $definition;
                continue;
            }

            if (! $definition instanceof RouteDefinition) {
                throw new UnexpectedValueException(\vsprintf("%s Not Instance Of %s", [
                    \get_debug_type($definition),
                    RouteDefinition::class,
                ]));
            }

            yield $definition;
        }
    }

    public function __serialize(): array
    {
        return [
            'definitions' => $this->definitions,
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->definitions = $data['definitions'];
    }
}
