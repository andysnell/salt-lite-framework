<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\App;

class Environment
{
    public readonly BuildStage $stage;

    /**
     * @param array<string, mixed> $server
     */
    public function __construct(
        public readonly array $server,
        public readonly Context $context,
    ) {
        $this->stage = BuildStage::tryFrom(
            $this->server['SALT_BUILD_STAGE'] ?? BuildStage::Production->value,
        ) ?? BuildStage::Production;
    }
}
