<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Tests\Util\Cryptography\Asymmetric\Algorithm;

use PhoneBurner\SaltLite\Framework\Util\Cryptography\Asymmetric\Algorithm\X25519XSalsa20Poly1305;
use PhoneBurner\SaltLite\Framework\Util\Cryptography\Asymmetric\EncryptionKeyPair;
use PhoneBurner\SaltLite\Framework\Util\Cryptography\Asymmetric\EncryptionPublicKey;
use PhoneBurner\SaltLite\Framework\Util\Cryptography\Exception\CryptoLogicException;
use PhoneBurner\SaltLite\Framework\Util\Cryptography\String\Ciphertext;
use PhoneBurner\SaltLite\Framework\Util\Cryptography\String\ConstantTimeEncoding;
use PhoneBurner\SaltLite\Framework\Util\Helper\File;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class X25519XSalsa20Poly1305Test extends TestCase
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

        $ciphertext = X25519XSalsa20Poly1305::encrypt($sender_keypair, $recipient_keypair->public, self::MESSAGE);
        $plaintext = X25519XSalsa20Poly1305::decrypt($recipient_keypair, $sender_keypair->public, $ciphertext);

        // Assert the decrypted message matches the original message
        self::assertSame(self::MESSAGE, $plaintext);

        // Assert the ciphertext is not the same as the plaintext
        self::assertNotSame(self::MESSAGE, $ciphertext);

        // Assert encrypting with the same message and key does not produce the same ciphertext
        self::assertNotSame($ciphertext, X25519XSalsa20Poly1305::encrypt(
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
        $ciphertext = CipherText::import(File::get(__DIR__ . '/../../Fixtures/lorem_x25519xsalsa20poly1305.txt'));

        $plaintext = X25519XSalsa20Poly1305::decrypt($recipient_keypair, $sender_keypair->public, $ciphertext);

        self::assertNotNull($plaintext);
        self::assertStringEqualsFile(__DIR__ . '/../../Fixtures/lorem.txt', $plaintext);
    }

    #[Test]
    public function anonymous_encryption_regression_test(): void
    {
        $recipient_keypair = EncryptionKeyPair::import(self::KNOWN_RECIPIENT_KEYPAIR);
        $ciphertext = CipherText::import(File::get(__DIR__ . '/../../Fixtures/lorem_x25519xsalsa20poly1305_anonymous.txt'));

        $plaintext = X25519XSalsa20Poly1305::unseal($recipient_keypair, $ciphertext);

        self::assertNotNull($plaintext);
        self::assertStringEqualsFile(__DIR__ . '/../../Fixtures/lorem.txt', $plaintext);
    }

    #[Test]
    public function anonymous_encryption_happy_path(): void
    {
        $recipient_keypair = EncryptionKeyPair::generate();

        $ciphertext = X25519XSalsa20Poly1305::seal($recipient_keypair->public, self::MESSAGE);
        $plaintext = X25519XSalsa20Poly1305::unseal($recipient_keypair, $ciphertext);

        self::assertSame(self::MESSAGE, $plaintext);
    }

    #[Test]
    public function encrypt_throw_exception_if_additional_data_is_nonempty(): void
    {
        $sender_keypair = EncryptionKeyPair::generate();
        $recipient_keypair = EncryptionKeyPair::generate();

        $this->expectException(CryptoLogicException::class);
        $this->expectExceptionMessage('X25519-XSalsa20-Poly1305 is not an AEAD Construction');
        X25519XSalsa20Poly1305::encrypt($sender_keypair, $recipient_keypair->public, self::MESSAGE, 'Additional Metadata');
    }

    #[Test]
    public function decrypt_throw_exception_if_additional_data_is_nonempty(): void
    {
        $sender_keypair = EncryptionKeyPair::generate();
        $recipient_keypair = EncryptionKeyPair::generate();
        $this->expectException(CryptoLogicException::class);
        $this->expectExceptionMessage('X25519-XSalsa20-Poly1305 is not an AEAD Construction');
        X25519XSalsa20Poly1305::decrypt($sender_keypair, $recipient_keypair->public, new Ciphertext(\random_bytes(1024)), 'Additional Metadata');
    }

    #[Test]
    public function seal_throw_exception_if_additional_data_is_nonempty(): void
    {
        $recipient_keypair = EncryptionKeyPair::generate();

        $this->expectException(CryptoLogicException::class);
        $this->expectExceptionMessage('X25519-XSalsa20-Poly1305 is not an AEAD Construction');
        X25519XSalsa20Poly1305::seal($recipient_keypair->public, self::MESSAGE, self::ADDITIONAL_DATA);
    }

    #[Test]
    public function unseal_throw_exception_if_additional_data_is_nonempty(): void
    {
        $recipient_keypair = EncryptionKeyPair::generate();

        $this->expectException(CryptoLogicException::class);
        $this->expectExceptionMessage('X25519-XSalsa20-Poly1305 is not an AEAD Construction');
        X25519XSalsa20Poly1305::unseal($recipient_keypair, new Ciphertext(\random_bytes(1024)), self::ADDITIONAL_DATA);
    }

    #[Test]
    public function sodium_crypto_box_compatiblity_regression_test_sender(): void
    {
        // We generate a keypair using our own implementation and provide our public key
        // to the recipient as a hex/base64/base64url encoded string.
        $sender_keypair = EncryptionKeyPair::generate();
        $encoded_sender_public_key = $sender_keypair->public->export(ConstantTimeEncoding::Base64);

        // The recipient keypair is generated using sodium_crypto_box_keypair()
        // and they provide the public key to us as a base64 encoded string.
        $recipient_keypair_bytes = \sodium_crypto_box_keypair();
        $encoded_recipient_public_key = \sodium_bin2base64(
            \sodium_crypto_box_publickey($recipient_keypair_bytes),
            \SODIUM_BASE64_VARIANT_ORIGINAL,
        );

        // We encrypt and encode a message using our own implementation and send it with our public key
        $encoded_ciphertext = X25519XSalsa20Poly1305::encrypt(
            $sender_keypair,
            EncryptionPublicKey::import($encoded_recipient_public_key),
            self::MESSAGE,
        )->export(ConstantTimeEncoding::Base64);

        // The recipient must be able to decode the sender's public key and the ciphertext
        // using the default sodium_crypto_box_open() function. They do need to
        // know that the first 24-bytes of the ciphertext are the nonce.
        $ciphertext_bytes = \sodium_base642bin($encoded_ciphertext, \SODIUM_BASE64_VARIANT_ORIGINAL);
        $plaintext = \sodium_crypto_box_open(
            \substr($ciphertext_bytes, \SODIUM_CRYPTO_BOX_NONCEBYTES),
            \substr($ciphertext_bytes, 0, \SODIUM_CRYPTO_BOX_NONCEBYTES),
            \sodium_crypto_box_keypair_from_secretkey_and_publickey(
                \sodium_crypto_box_secretkey($recipient_keypair_bytes),
                \sodium_base642bin($encoded_sender_public_key, \SODIUM_BASE64_VARIANT_ORIGINAL),
            ),
        );

        self::assertSame(self::MESSAGE, $plaintext);
    }

    #[Test]
    public function sodium_crypto_box_compatiblity_regression_test_recipient(): void
    {
        // We generate a keypair using our own implementation and provide our public key
        // to the recipient as a hex/base64/base64url encoded string.
        $sender_keypair = EncryptionKeyPair::generate();
        $encoded_sender_public_key = $sender_keypair->public->export(ConstantTimeEncoding::Base64);

        // The recipient keypair is generated using sodium_crypto_box_keypair()
        // and they provide the public key to us as a base64 encoded string.
        $recipient_keypair_bytes = \sodium_crypto_box_keypair();
        $encoded_recipient_public_key = \sodium_bin2base64(
            \sodium_crypto_box_publickey($recipient_keypair_bytes),
            \SODIUM_BASE64_VARIANT_ORIGINAL,
        );

        // They encrypt and encode a message using the default sodium_crypto_box() function
        $nonce = \random_bytes(\SODIUM_CRYPTO_BOX_NONCEBYTES);
        $ciphertext = \sodium_crypto_box(
            self::MESSAGE,
            $nonce,
            \sodium_crypto_box_keypair_from_secretkey_and_publickey(
                \sodium_crypto_box_secretkey($recipient_keypair_bytes),
                \sodium_base642bin($encoded_sender_public_key, \SODIUM_BASE64_VARIANT_ORIGINAL),
            ),
        );

        // They prepend the nonce to the ciphertext and encode it as a base64 string
        $encoded_ciphertext = \sodium_bin2base64($nonce . $ciphertext, \SODIUM_BASE64_VARIANT_ORIGINAL);

        // We must be able to decode and decrypt the ciphertext using our own implementation
        $plaintext = X25519XSalsa20Poly1305::decrypt(
            $sender_keypair,
            EncryptionPublicKey::import($encoded_recipient_public_key),
            Ciphertext::import($encoded_ciphertext),
        );

        self::assertSame(self::MESSAGE, $plaintext);
    }
}
