<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Util\Filesystem;

use League\Flysystem\UnableToCreateDirectory;
use League\Flysystem\UnableToWriteFile;
use PhoneBurner\SaltLite\Framework\Util\Iterator\StreamIterator;
use Psr\Http\Message\StreamInterface;

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
    public const int CHUNK_BYTES = 8192;

    /**
     * Note: technically, this method could be used to read file contents from a
     * PSR-7 StreamInterface, since those are stringable, however, it would read
     * all the contents into memory in doing so, making the dedicated
     *
     * @param \Stringable|string $filename Typedef includes \SplFileInfo via \Stringable
     */
    public static function string(\Stringable|string $filename, \Stringable|string $contents): bool
    {
        $filename = self::normalizeFilename($filename);
        self::checkFilePermissions($filename);
        self::checkDirectoryPermissions($filename);
        $temp_file = self::createTempFilename($filename);
        return \file_put_contents($temp_file, (string)$contents)
            && \rename($temp_file, $filename);
    }

    public static function stream(\Stringable|string $filename, StreamInterface $stream): bool
    {
        return self::iterable($filename, new StreamIterator($stream));
    }

    /**
     * @param iterable<iterable<string|\Stringable>|string|\Stringable> $pump
     */
    public static function iterable(\Stringable|string $filename, iterable $pump): bool
    {
        $filename = self::normalizeFilename($filename);
        self::checkFilePermissions($filename);
        self::checkDirectoryPermissions($filename);
        $temp_file = self::createTempFilename($filename);
        return self::pump(new \SplFileObject($temp_file, FileMode::ReadWriteCreateNewOrTruncateExisting->value), $pump)
            && \rename($temp_file, $filename);
    }

    /**
     * Note, if we are passed an instance of \SplFileInfo as the $filename,
     * we need to explicitly call `getPathname()` instead of just casting it
     * to a string in order to account for how \SplFileObject instances return
     * the current line from the file.
     */
    private static function normalizeFilename(\Stringable|string $filename): string
    {
        return match (true) {
            \is_string($filename) => $filename,
            $filename instanceof \SplFileInfo => $filename->getPathname(),
            default => (string)$filename,
        };
    }

    private static function createTempFilename(string $filename): string
    {
        return $filename . '.' . \bin2hex(\random_bytes(8));
    }

    /**
     * If the file doesn't exist, no problem. Otherwise, we need to check that
     * the "filename" is both a regular file and writable.
     */
    private static function checkFilePermissions(string $filename): true
    {
        return match (true) {
            ! \file_exists($filename) => true,
            \is_writable($filename) => \is_file($filename) ?: throw UnableToWriteFile::atLocation(
                $filename,
                'The location exists and is writable, but is not a regular file.',
            ),
            default => throw UnableToWriteFile::atLocation(
                $filename,
                'The file already exists, but it is not writable.',
            ),
        };
    }

    /**
     * If the directory file would be written to does not exist, this will try
     * to recursively create parent directories.
     */
    private static function checkDirectoryPermissions(string $filename): true
    {
        $directory = \dirname($filename);
        return match (true) {
            \is_writable($directory) => \is_dir($directory) ?: throw UnableToCreateDirectory::atLocation(
                $directory,
                'The location already exists, but is not a directory.',
            ),
            \file_exists($directory) => throw UnableToCreateDirectory::atLocation(
                $directory,
                'The directory already exists, but it is not writable.',
            ),
            \mkdir($directory, recursive: true), \is_dir($directory) => true,
            default => throw UnableToCreateDirectory::atLocation(
                $directory,
                'The directory does not exist, but could not be created.',
            ),
        };
    }

    /**
     * @param iterable<iterable<string|\Stringable>|string|\Stringable> $pump
     */
    private static function pump(\SplFileObject $stream, iterable $pump): int
    {
        $bytes = 0;
        foreach ($pump as $chunk) {
            $bytes += \is_iterable($chunk) ? self::pump($stream, $chunk) : $stream->fwrite((string)$chunk);
        }
        return $bytes;
    }
}
