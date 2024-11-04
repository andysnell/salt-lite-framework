<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Util\Filesystem;

/**
 * Atomic file writing utility
 *
 * In order to avoid a potential race condition between multiple execution
 * threads trying to write a new file to the same local memory location
 * when the file does not exist, and ending up with an unparsable "broken" file,
 * we first write to a temporary file and then rename (i.e. `mv`) it to the
 * actual file. Since we're renaming a file on the same file system and in
 * the same directory, this should be an atomic operation and avoid permissions
 * issues. If multiple threads try to do this simultaneously, the last write
 * wins, while any attempts to read the file during the rename operation will
 * be successful using the file-to-be-overwritten.
 */
class FileWriter
{
    public static function string(\Stringable|string $filename, \Stringable|string $contents): bool
    {
        $temp_file = $filename . '.' . \bin2hex(\random_bytes(8));
        return \file_put_contents($temp_file, (string)$contents)
            && \rename($temp_file, (string)$filename);
    }

    /**
     * @param iterable<iterable<string|\Stringable>|string|\Stringable> $pump
     */
    public static function iterable(\Stringable|string $filename, iterable $pump): bool
    {
        $temp_file = $filename . '.' . \bin2hex(\random_bytes(8));
        return self::pump(new \SplFileObject($temp_file, 'w+b'), $pump)
            && \rename($temp_file, (string)$filename);
    }

    /**
     * @param iterable<iterable<string|\Stringable>|string|\Stringable> $pump
     */
    private static function pump(\SplFileObject $stream, iterable $pump): int
    {
        $bytes = 0;
        foreach ($pump as $chunk) {
            $bytes += \is_iterable($chunk) ? self::pump($stream, $chunk) : (int)$stream->fwrite((string)$chunk);
        }
        return $bytes;
    }
}
