<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Routing\Definition;

class UriTemplate implements \Stringable
{
    public function __construct(private readonly string $path)
    {
    }

    public function render(array $variables): string
    {
        return (string)$this->applyVariables($variables)
            ->removeOptionalSegmentsWithUnsetVariables()
            ->removeUnsetVariables()
            ->removeBracketsFromOptionalSegments();
    }

    private function applyVariables(array $variables): self
    {
        $pattern = [];
        $replacement = [];

        foreach ($variables as $var => $value) {
            $pattern[] = '#{' . $var . '[^}]*}#';
            $replacement[] = $value;
        }

        return new self((string)\preg_replace($pattern, $replacement, $this->path));
    }

    private function removeBracketsFromOptionalSegments(): self
    {
        return new self(\str_replace(['[', ']'], '', $this->path));
    }

    private function removeOptionalSegmentsWithUnsetVariables(): self
    {
        return new self((string)\preg_replace('#\[[^\]]*{\w+[^}]*}[^\]]*\]#', '', $this->path));
    }

    private function removeUnsetVariables(): self
    {
        return new self((string)\preg_replace('#{[^}]*}#', '', $this->path));
    }

    #[\Override]
    public function __toString(): string
    {
        return $this->path;
    }
}
