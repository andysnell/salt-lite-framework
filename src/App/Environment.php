<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\App;

use PhoneBurner\SaltLite\Framework\Util\Attribute\DefaultServiceProvider;

#[DefaultServiceProvider(AppServiceProvider::class)]
class Environment
{
    public readonly BuildStage $stage;

    /**
     * @param array<string, mixed> $server
     */
    public function __construct(
        public readonly Context $context,
        public readonly string $root = '',
        private array|null &$server = null,
        private array|null &$env = null,
    ) {
        $this->server ??= $_SERVER;
        $this->env ??= $_ENV;
        $this->stage = BuildStage::instance($this->server['SALT_BUILD_STAGE'] ?? BuildStage::Production);
    }

    public function root(): string
    {
        return $this->root;
    }

    public function server(
        string $key,
        mixed $production = null,
        mixed $development = null,
        mixed $integration = null,
    ): string|int|float|bool|null {
        return self::cast($this->server[$key] ?? null) ?? $this->match($production, $development, $integration);
    }

    public function env(
        string $key,
        mixed $production = null,
        mixed $development = null,
        mixed $integration = null,
    ): string|int|float|bool|null {
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

    private static function cast(string|int|float|bool|null $value): string|int|float|bool|null
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
