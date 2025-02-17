<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Util\Serialization;

use PhoneBurner\SaltLite\Framework\Domain\Time\Standards\Rfc3339;
use PhoneBurner\SaltLite\Framework\Util\Filesystem\FileWriter;
use Symfony\Component\VarExporter\VarExporter;

/**
 * Export a value to opcache friendly PHP code, like var_export(), uses Symfony
 * VarExporter under the hood
 */
class VarExport
{
    const string FILE_EXPORT_TEMPLATE = <<<'PHP'
            <?php
            
            /**
             * %s (%s)
             */
            
            declare(strict_types=1);
            
            return %s;

            PHP;

    /**
     * Exports a value to a file, adding the PHP opening tag, a header message,
     * and a timestamp before returning the value.
     */
    public static function toFile(
        \Stringable|string $filename,
        mixed $value,
        string $header_message = 'Generated File',
        \DateTimeInterface $timestamp = new \DateTimeImmutable(),
    ): void {
        FileWriter::string($filename, \vsprintf(self::FILE_EXPORT_TEMPLATE, [
            $header_message,
            $timestamp->format(Rfc3339::DATETIME),
            VarExporter::export($value),
        ]));
    }

    public static function toString(mixed $value): string
    {
        return VarExporter::export($value);
    }
}
