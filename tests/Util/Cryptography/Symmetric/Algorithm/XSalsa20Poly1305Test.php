<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Tests\Util\Cryptography\Symmetric\Algorithm;

use PhoneBurner\SaltLite\Framework\Util\Cryptography\Exception\CryptoLogicException;
use PhoneBurner\SaltLite\Framework\Util\Cryptography\String\Ciphertext;
use PhoneBurner\SaltLite\Framework\Util\Cryptography\String\ConstantTimeEncoding;
use PhoneBurner\SaltLite\Framework\Util\Cryptography\Symmetric\Algorithm\XSalsa20Poly1305;
use PhoneBurner\SaltLite\Framework\Util\Cryptography\Symmetric\SharedKey;
use PhoneBurner\SaltLite\Framework\Util\Helper\File;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

final class XSalsa20Poly1305Test extends TestCase
{
    public const string MESSAGE = 'The Quick Brown Fox Jumps Over The Lazy Dog';

    public const string KNOWN_KEY = 'pP8fF46Eb737WAN9ccW1iZJP3w/7GESMKgfWT38/aU0=';

    #[Test]
    public function happy_path(): void
    {
        $shared_key = SharedKey::generate();

        $ciphertext = XSalsa20Poly1305::encrypt($shared_key, self::MESSAGE);

        $plaintext = XSalsa20Poly1305::decrypt($shared_key, $ciphertext);

        self::assertSame(self::MESSAGE, $plaintext);
    }

    #[Test]
    public function symmetric_encryption_regression_test(): void
    {
        $shared_key = SharedKey::import(self::KNOWN_KEY);
        $ciphertext = CipherText::import(File::get(__DIR__ . '/../../Fixtures/lorem_xsalsa20poly1305.txt'));

        $plaintext = XSalsa20Poly1305::decrypt($shared_key, $ciphertext);

        self::assertNotNull($plaintext);
        self::assertStringEqualsFile(__DIR__ . '/../../Fixtures/lorem.txt', $plaintext);
    }

    #[Test]
    public function encrypt_throw_exception_if_additional_data_is_nonempty(): void
    {
        $shared_key = SharedKey::generate();

        $this->expectException(CryptoLogicException::class);
        $this->expectExceptionMessage('XSalsa20-Poly1305 is not an AEAD Construction');
        XSalsa20Poly1305::encrypt($shared_key, self::MESSAGE, 'Additional Metadata');
    }

    #[Test]
    public function decrypt_throw_exception_if_additional_data_is_nonempty(): void
    {
        $shared_key = SharedKey::generate();

        $this->expectException(CryptoLogicException::class);
        $this->expectExceptionMessage('XSalsa20-Poly1305 is not an AEAD Construction');
        XSalsa20Poly1305::decrypt($shared_key, new Ciphertext(\random_bytes(1024)), 'Additional Metadata');
    }

    #[Test]
    public function decrypt_returns_null_with_wrong_key(): void
    {
        $shared_key = SharedKey::generate();

        $ciphertext = XSalsa20Poly1305::encrypt($shared_key, self::MESSAGE);

        $wrong_key = SharedKey::generate();

        $plaintext = XSalsa20Poly1305::decrypt($wrong_key, $ciphertext);

        self::assertNull($plaintext);
    }

    #[Test]
    public function decrypt_returns_null_with_wrong_tag(): void
    {
        $shared_key = SharedKey::generate();

        $ciphertext = XSalsa20Poly1305::encrypt($shared_key, self::MESSAGE);
        $ciphertext = new Ciphertext(\substr($ciphertext->bytes(), 0, -1));

        $plaintext = XSalsa20Poly1305::decrypt($shared_key, $ciphertext);

        self::assertNull($plaintext);
    }

    #[Test]
    #[TestWith([''])]
    #[TestWith(['short'])]
    public function decrypt_returns_null_when_message_is_too_short(string $ciphertext): void
    {
        // Pass a deliberately short message to trigger error condition.
        $plaintext = XSalsa20Poly1305::decrypt(SharedKey::generate(), new Ciphertext($ciphertext));

        self::assertNull($plaintext);
    }

    #[Test]
    public function sodium_crypto_secretbox_compatibility_regression_test_sender(): void
    {
        $key_bytes = \random_bytes(\SODIUM_CRYPTO_SECRETBOX_KEYBYTES);

        // Encrypt and encode the message using our implementation
        $encoded_ciphertext = XSalsa20Poly1305::encrypt(new SharedKey($key_bytes), self::MESSAGE)
            ->export(ConstantTimeEncoding::Base64);

        // Decrypt and decode the message using sodium_crypto_secretbox()
        $ciphertext = \sodium_base642bin($encoded_ciphertext, \SODIUM_BASE64_VARIANT_ORIGINAL);
        $plaintext = \sodium_crypto_secretbox_open(
            \substr($ciphertext, \SODIUM_CRYPTO_SECRETBOX_NONCEBYTES),
            \substr($ciphertext, 0, \SODIUM_CRYPTO_SECRETBOX_NONCEBYTES),
            $key_bytes,
        );

        self::assertSame(self::MESSAGE, $plaintext);
    }

    #[Test]
    public function sodium_crypto_secretbox_compatibility_regression_test_recipient(): void
    {
        $key_bytes = \random_bytes(\SODIUM_CRYPTO_SECRETBOX_KEYBYTES);

        // Encrypt and encode the message using sodium_crypto_secretbox()
        $nonce = \random_bytes(\SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
        $ciphertext = \sodium_crypto_secretbox(self::MESSAGE, $nonce, $key_bytes);
        $encoded_ciphertext = \sodium_bin2base64($nonce . $ciphertext, \SODIUM_BASE64_VARIANT_ORIGINAL);

        // Encrypt and encode the message using our implementation
        $plaintext = XSalsa20Poly1305::decrypt(new SharedKey($key_bytes), Ciphertext::import($encoded_ciphertext));

        self::assertSame(self::MESSAGE, $plaintext);
    }
}
