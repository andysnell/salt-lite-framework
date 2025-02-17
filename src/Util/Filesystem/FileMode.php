<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Util\Filesystem;

enum FileMode: string
{
    /**
     * Open for reading only; place the file pointer at the beginning of the file.
     */
    case ReadOnly = 'r';

    /**
     * Open for reading and writing; place the file pointer at the beginning of the file.
     */
    case ReadWriteOverwriteExisting = 'r+';

    /**
     * Open for writing only; place the file pointer at the beginning of the
     * file and truncate the file to zero length. If the file does not exist,
     * attempt to create it.
     */
    case WriteOnlyCreateNewOrTruncateExisting = 'w';

    /**
     * Open for reading and writing; place the file pointer at the beginning of
     * the file and truncate the file to zero length. If the file does not
     * exist, attempt to create it.
     */
    case ReadWriteCreateNewOrTruncateExisting = 'w+';

    /**
     * Open for writing only; place the file pointer at the end of the file. If
     * the file does not exist, attempt to create it. In this mode, fseek() has
     * no effect, writes are always appended
     */
    case WriteOnlyCreateNewOrAppendExisting = 'a';

    /**
     * Open for reading and writing; place the file pointer at the end of the
     * file. If the file does not exist, attempt to create it. In this mode,
     * fseek() only affects the reading position, writes are always appended.
     */
    case ReadWriteCreateNewOrAppendExisting = 'a+';

    /**
     * Create and open for writing only; place the file pointer at the beginning
     * of the file. If the file already exists, the fopen() call will fail by
     * returning false and generating an error of level E_WARNING. If the file
     * does not exist, attempt to create it. This is equivalent to specifying
     * O_EXCL|O_CREAT flags for the underlying open(2) system call.
     */
    case WriteOnlyCreateNewOnly = 'x';

    /**
     * Create and open for reading and writing; place the file pointer at the beginning
     * of the file. If the file already exists, the fopen() call will fail by
     * returning false and generating an error of level E_WARNING. If the file
     * does not exist, attempt to create it. This is equivalent to specifying
     * O_EXCL|O_CREAT flags for the underlying open(2) system call.
     */
    case ReadWriteCreateNewOnly = 'x+';

    /**
     * Open the file for writing only. If the file does not exist, it is
     * created. If it exists, it is neither truncated (as opposed to 'w'), nor
     * the call to this function fails (as is the case with 'x'). The file
     * pointer is positioned on the beginning of the file. This may be useful
     * if it's desired to get an advisory lock (see flock()) before attempting
     * to modify the file, as using 'w' could truncate the file before the lock
     * was obtained (if truncation is desired, ftruncate() can be used after
     * the lock is requested).
     */
    case WriteOnlyCreateNewOrOverwriteExisting = 'c';

    /**
     * Open the file for reading and writing. If the file does not exist, it is
     * created. If it exists, it is neither truncated (as opposed to 'w'), nor
     * the call to this function fails (as is the case with 'x'). The file
     * pointer is positioned on the beginning of the file. This may be useful
     * if it's desired to get an advisory lock (see flock()) before attempting
     * to modify the file, as using 'w' could truncate the file before the lock
     * was obtained (if truncation is desired, ftruncate() can be used after
     * the lock is requested).
     */
    case ReadWriteCreateNewOrOverwriteExisting = 'c+';

    /** Case Insensitive Matching */
    public static function instance(mixed $value): self
    {
        return self::cast($value) ?? throw new \InvalidArgumentException();
    }

    /**
     * Note: we can't use the trait version of this method because we need to support
     * string values that might have the 'b' or 't' flags appended to them.
     */
    public static function cast(mixed $value): self|null
    {
        return match (true) {
            $value instanceof self, $value === null => $value,
            \is_string($value), $value instanceof \Stringable => self::tryFrom(
                \trim(\str_replace(['b', 't'], '', \strtolower((string)$value))),
            ),
            default => null,
        };
    }
}
