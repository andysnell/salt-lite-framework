<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Mailer;

readonly class Attachment
{
    public AttachmentType $type;

    public function __construct(
        public string $path = '',
        public string $content = '',
        public string|null $name = null,
        public string|null $content_type = null,
        public bool $inline = false,
    ) {
        if ($this->path === '' && $this->content === '') {
            throw new \InvalidArgumentException('Attachment must have a file path or content');
        }

        if ($this->path !== '' && $this->content !== '') {
            throw new \InvalidArgumentException('Attachment cannot have both a file path and content');
        }

        $this->type = match (true) {
            $this->content !== '' => $this->inline ? AttachmentType::EmbedFromContent : AttachmentType::AttachFromContent,
            $this->path !== '' => $this->inline ? AttachmentType::EmbedFromPath : AttachmentType::AttachFromPath,
            default => throw new \InvalidArgumentException('Cannot Determine Attachment Type'),
        };
    }

    public static function fromPath(
        string $path,
        string|null $name = null,
        string|null $content_type = null,
        bool $inline = false,
    ): self {
        return new self(path: $path, name: $name, content_type: $content_type, inline: $inline);
    }

    public static function fromContent(
        string $content,
        string|null $name = null,
        string|null $content_type = null,
        bool $inline = false,
    ): self {
        return new self(content: $content, name: $name, content_type: $content_type, inline: $inline);
    }
}
