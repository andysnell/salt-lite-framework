<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\App;

use PhoneBurner\SaltLite\Framework\Util\Attribute\DefaultServiceProvider;
use Psr\Container\ContainerInterface;

/**
 * Represents the environment and context in which the application is running.
 * This class is a container that that holds server and environment variables.
 * (The server is checked first before the environment.) It also defines some
 * methods for getting things like the root directory and hostname.
 */
#[DefaultServiceProvider(AppServiceProvider::class)]
final class Environment implements ContainerInterface
{
    public readonly BuildStage $stage;

    /**
     * @param array<string, mixed> $server Since this will usually be $_SERVER, it cannot be readonly
     * @param array<string, mixed> $env Since this will usually be $_ENV, it cannot be readonly
     */
    public function __construct(
        public readonly Context $context,
        public readonly string $root,
        private array &$server,
        private array &$env,
    ) {
        $this->stage = BuildStage::instance($this->server['SALT_BUILD_STAGE'] ?? BuildStage::Production);
    }

    public function root(): string
    {
        return $this->root;
    }

    public function hostname(): string
    {
        return \gethostname() ?: 'localhost';
    }

    public function has(string $id): bool
    {
        return isset($this->server[$id]) || isset($this->env[$id]);
    }

    public function get(string $id): mixed
    {
        return $this->server[$id] ?? $this->env[$id] ?? null;
    }

    public function server(
        string $key,
        mixed $production = null,
        mixed $development = null,
        mixed $integration = null,
    ): \UnitEnum|string|int|float|bool|null {
        return self::cast($this->server[$key] ?? null) ?? $this->match($production, $development, $integration);
    }

    public function env(
        string $key,
        mixed $production = null,
        mixed $development = null,
        mixed $integration = null,
    ): \UnitEnum|string|int|float|bool|null {
        return self::cast($this->env[$key] ?? null) ?? $this->match($production, $development, $integration);
    }

    public function match(mixed $production, mixed $development = null, mixed $integration = null): mixed
    {
        return match ($this->stage) {
            BuildStage::Production => $production,
            BuildStage::Integration => $integration ?? $production,
            BuildStage::Development => $development ?? $integration ?? $production,
        };
    }

    private static function cast(\UnitEnum|string|int|float|bool|null $value): \UnitEnum|string|int|float|bool|null
    {
        return \is_string($value) ? match (\strtolower($value)) {
            'true', 'yes', 'on' => true,
            'false', 'no', 'off' => false,
            'null', '' => null,
            '0' => 0,
            '1' => 1,
            default => \filter_var($value, \FILTER_VALIDATE_INT, \FILTER_NULL_ON_FAILURE)
                ?? \filter_var($value, \FILTER_VALIDATE_FLOAT, \FILTER_NULL_ON_FAILURE)
                ?? $value,
        } : $value;
    }
}
