<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Tests\Util\Cryptography\Asymmetric\Algorithm;

use PhoneBurner\SaltLite\Framework\Util\Cryptography\Asymmetric\Algorithm\X25519XChaCha20Blake2b;
use PhoneBurner\SaltLite\Framework\Util\Cryptography\Asymmetric\EncryptionKeyPair;
use PhoneBurner\SaltLite\Framework\Util\Cryptography\String\Ciphertext;
use PhoneBurner\SaltLite\Framework\Util\Cryptography\String\ConstantTimeEncoding;
use PhoneBurner\SaltLite\Framework\Util\Helper\File;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class X25519XChaCha20Blake2bTest extends TestCase
{
    public const string MESSAGE = 'The Quick Brown Fox Jumps Over The Lazy Dog';

    public const string KNOWN_SENDER_KEYPAIR = 'kk72c6s2di5fKvBXLSbYCISOvj+a26p3nhe/+TzTi3osLpeqgv2ChN/RzsZskMYLU7jct02PprzdoHPeUwt5Kg==';

    public const string KNOWN_RECIPIENT_KEYPAIR = 'fvVzvZ085EQ+chb5HtMzBhLcBHjVAQi1g4CnQfuJnjTGPBGm6sIenWqy7v7b4iNdaQhtpn6gDVtpXquKyo7KKQ==';

    public const string ADDITIONAL_DATA = 'Some Random Metadata Not Sent in the Message';

    #[Test]
    public function encryption_happy_path(): void
    {
        $sender_keypair = EncryptionKeyPair::generate();
        $recipient_keypair = EncryptionKeyPair::generate();

        $ciphertext = X25519XChaCha20Blake2b::encrypt($sender_keypair, $recipient_keypair->public, self::MESSAGE);
        $plaintext = X25519XChaCha20Blake2b::decrypt($recipient_keypair, $sender_keypair->public, $ciphertext);

        // Assert the decrypted message matches the original message
        self::assertSame(self::MESSAGE, $plaintext);

        // Assert the ciphertext is not the same as the plaintext
        self::assertNotSame(self::MESSAGE, $ciphertext);

        // Assert encrypting with the same message and key does not produce the same ciphertext
        self::assertNotSame($ciphertext, X25519XChaCha20Blake2b::encrypt(
            $sender_keypair,
            $recipient_keypair->public,
            self::MESSAGE,
        ));
    }

    #[Test]
    public function authenticated_encryption_regression_test(): void
    {
        $sender_keypair = EncryptionKeyPair::import(self::KNOWN_SENDER_KEYPAIR);
        $recipient_keypair = EncryptionKeyPair::import(self::KNOWN_RECIPIENT_KEYPAIR);
        $ciphertext = CipherText::import(File::get(__DIR__ . '/../../Fixtures/lorem_x25519xchacha20blake2b.txt'));

        $plaintext = X25519XChaCha20Blake2b::decrypt($recipient_keypair, $sender_keypair->public, $ciphertext);

        self::assertNotNull($plaintext);
        self::assertStringEqualsFile(__DIR__ . '/../../Fixtures/lorem.txt', $plaintext);
    }

    #[Test]
    public function anonymous_encryption_regression_test(): void
    {
        $recipient_keypair = EncryptionKeyPair::import(self::KNOWN_RECIPIENT_KEYPAIR);
        $ciphertext = CipherText::import(File::get(__DIR__ . '/../../Fixtures/lorem_x25519xchacha20blake2b_anonymous.txt'), ConstantTimeEncoding::Base64);

        $plaintext = X25519XChaCha20Blake2b::unseal($recipient_keypair, $ciphertext);

        self::assertNotNull($plaintext);
        self::assertStringEqualsFile(__DIR__ . '/../../Fixtures/lorem.txt', $plaintext);
    }

    #[Test]
    public function anonymous_encryption_happy_path(): void
    {
        $recipient_keypair = EncryptionKeyPair::generate();

        $ciphertext = X25519XChaCha20Blake2b::seal($recipient_keypair->public, self::MESSAGE);
        $plaintext = X25519XChaCha20Blake2b::unseal($recipient_keypair, $ciphertext);

        self::assertSame(self::MESSAGE, $plaintext);
    }

    #[Test]
    public function aead_happy_path(): void
    {
        $sender_keypair = EncryptionKeyPair::generate();
        $recipient_keypair = EncryptionKeyPair::generate();

        $ciphertext = X25519XChaCha20Blake2b::encrypt(
            $sender_keypair,
            $recipient_keypair->public,
            self::MESSAGE,
            self::ADDITIONAL_DATA,
        );

        $plaintext = X25519XChaCha20Blake2b::decrypt(
            $recipient_keypair,
            $sender_keypair->public,
            $ciphertext,
            self::ADDITIONAL_DATA,
        );

        // Assert the decrypted message matches the original message
        self::assertSame(self::MESSAGE, $plaintext);
    }

    #[Test]
    public function aead_missing_on_encryption(): void
    {
        $sender_keypair = EncryptionKeyPair::generate();
        $recipient_keypair = EncryptionKeyPair::generate();

        $ciphertext = X25519XChaCha20Blake2b::encrypt(
            $sender_keypair,
            $recipient_keypair->public,
            self::MESSAGE,
        );

        $plaintext = X25519XChaCha20Blake2b::decrypt(
            $recipient_keypair,
            $sender_keypair->public,
            $ciphertext,
            self::ADDITIONAL_DATA,
        );

        self::assertNull($plaintext);
    }

    #[Test]
    public function aead_missing_on_decryption(): void
    {
        $sender_keypair = EncryptionKeyPair::generate();
        $recipient_keypair = EncryptionKeyPair::generate();

        $ciphertext = X25519XChaCha20Blake2b::encrypt(
            $sender_keypair,
            $recipient_keypair->public,
            self::MESSAGE,
            self::ADDITIONAL_DATA,
        );

        $plaintext = X25519XChaCha20Blake2b::decrypt(
            $recipient_keypair,
            $sender_keypair->public,
            $ciphertext,
        );

        self::assertNull($plaintext);
    }

    #[Test]
    public function aead_does_not_match(): void
    {
        $sender_keypair = EncryptionKeyPair::generate();
        $recipient_keypair = EncryptionKeyPair::generate();

        $ciphertext = X25519XChaCha20Blake2b::encrypt(
            $sender_keypair,
            $recipient_keypair->public,
            self::MESSAGE,
            self::ADDITIONAL_DATA,
        );

        $plaintext = X25519XChaCha20Blake2b::decrypt(
            $recipient_keypair,
            $sender_keypair->public,
            $ciphertext,
            "Some Other Metadata",
        );

        self::assertNull($plaintext);
    }
}
