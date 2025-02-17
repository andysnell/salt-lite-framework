<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Util\Helper;

use PhoneBurner\SaltLite\Framework\Util\Filesystem\FileMode;
use Psr\Http\Message\StreamInterface;

use function PhoneBurner\SaltLite\Framework\null_if_false;

class File
{
    public static function get(\Stringable|string $filename): string
    {
        return \file_get_contents((string)$filename) ?: throw new \RuntimeException('Could Not Read File');
    }

    /**
     * @return resource stream
     */
    public static function open(
        \Stringable|string $filename,
        FileMode $mode = FileMode::ReadOnly,
        mixed $context = null,
    ): mixed {
        $filename = match (true) {
            \is_string($filename) => $filename,
            $filename instanceof \SplFileInfo => $filename->getPathname(),
            default => (string)$filename,
        };

        $context = match (true) {
            $context === null => null,
            \is_resource($context) && \get_resource_type($context) === 'stream-context' => $context,
            default => throw new \InvalidArgumentException('context must be null or stream-context resource'),
        };

        $stream = \fopen($filename, $mode->value, false, $context);
        if ($stream === false) {
            throw new \RuntimeException('Could Not Create Stream');
        }

        return $stream;
    }

    public static function read(): string
    {
        return '';
    }

    public static function write(string $content): int
    {
        return 0;
    }

    public static function size(mixed $value): int
    {
        return match (true) {
            Type::isStreamResource($value) => \fstat($value)['size'] ?? null,
            $value instanceof StreamInterface => $value->getSize(),
            $value instanceof \SplFileInfo => null_if_false($value->getSize()),
            \is_string($value) && \file_exists($value) => null_if_false(\filesize($value)),
            default => throw new \InvalidArgumentException('Unsupported Type:' . \get_debug_type($value)),
        } ?? throw new \RuntimeException('Unable to Get Size of Stream');
    }

    public static function close(mixed $value): void
    {
        match (true) {
            Type::isStreamResource($value) => \fclose($value),
            $value instanceof StreamInterface => $value->close(),
            default => null,
        };
    }
}
