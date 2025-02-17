<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Util\Cryptography\String\Traits;

use PhoneBurner\SaltLite\Framework\Util\Cryptography\String\BinaryString;
use PhoneBurner\SaltLite\Framework\Util\Cryptography\String\ConstantTimeEncoding;

/**
 * @phpstan-require-implements BinaryString
 */
trait BinaryStringImportExportBehavior
{
    public static function import(
        #[\SensitiveParameter] string $string,
        ConstantTimeEncoding|null $encoding = null,
    ): static {
        return new static(($encoding ?? static::DEFAULT_ENCODING)->decode($string));
    }

    public function export(
        ConstantTimeEncoding|null $encoding = null,
        bool $prefix = false,
    ): string {
        return ($encoding ?? static::DEFAULT_ENCODING)->encode($this->bytes(), $prefix);
    }

    public static function tryImport(
        #[\SensitiveParameter] string|null $string,
        ConstantTimeEncoding|null $encoding = null,
    ): static|null {
        try {
            // shortcut when the id is null or the empty string
            return $string ? static::import($string, ($encoding ?? static::DEFAULT_ENCODING)) : null;
        } catch (\Throwable) {
            return null;
        }
    }
}
