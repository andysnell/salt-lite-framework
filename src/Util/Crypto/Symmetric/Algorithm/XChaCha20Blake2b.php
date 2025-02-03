<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Util\Crypto\Symmetric\Algorithm;

use PhoneBurner\SaltLite\Framework\Util\Attribute\Internal;
use PhoneBurner\SaltLite\Framework\Util\Crypto\Encoding;
use PhoneBurner\SaltLite\Framework\Util\Crypto\Exception\CryptoRuntimeException;
use PhoneBurner\SaltLite\Framework\Util\Crypto\Exception\InvalidMessage;
use PhoneBurner\SaltLite\Framework\Util\Crypto\Key;
use PhoneBurner\SaltLite\Framework\Util\Crypto\Symmetric\SharedKey;
use PhoneBurner\SaltLite\Framework\Util\Crypto\Util;

/**
 * Authenticated Symmetric Encryption with Assosicated Data (AEAD) algorithm
 * using XChaCha20 and BLAKE2b-MAC
 *
 * This class is not intended to be used directly. Instead, use the
 * `SymmetricEncryption` class.
 *
 * This is a modern approach that addresses several shortcomings
 *  around both the `\sodium_crypto_secretbox()` and \sodium_crypto_aead_*() APIs.
 *  Specifically, the algorithm below mitigates:
 *   - Chosen-Ciphertext Attacks
 *   - Key-Commitment aka "Invisible Salamander" attacks
 *   - Timing Attacks on the MAC
 *
 * Notes:
 *  - Follows "encrypt-then-MAC" pattern (https://moxie.org/2011/12/13/the-cryptographic-doom-principle.html)
 *  - Using a keyed BLAKE2b-MAC for authentication instead of Poly1305 makes the
 *    ciphertext "message commiting", and more resistant to "invisible salamander"
 *    attacks. (https://eprint.iacr.org/2020/1456)
 */
#[Internal]
class XChaCha20Blake2b
{
    /**
     * HKDF Info Parameters for Derived Keys (Encryption and Authentication)
     *
     * @see https://tools.ietf.org/html/rfc5869#section-3.2
     */
    public const string HKDF_SBOX_INFO = 'EncryptionKey';
    public const string HKDF_AUTH_INFO = 'AuthenticationKey';

    public const int NONCE_BYTES = \SODIUM_CRYPTO_SECRETBOX_NONCEBYTES;
    public const int HKDF_SALT_BYTES = \SODIUM_CRYPTO_GENERICHASH_BYTES;
    public const int AUTH_TAG_BYTES = \SODIUM_CRYPTO_GENERICHASH_BYTES;
    public const int MIN_CIPHERTEXT_BYTES = self::HKDF_SALT_BYTES + self::NONCE_BYTES + self::AUTH_TAG_BYTES;

    public static function encrypt(
        #[\SensitiveParameter] Key $key,
        #[\SensitiveParameter] string $plaintext,
        #[\SensitiveParameter] string $additional_data = '',
        Encoding $encoding = Encoding::Base64,
    ): string {
        try {
            // We need both a random salt for the HKDF key derivation process,
            // and a random nonce for the message encryption process.
            $hkdf_salt = \random_bytes(self::HKDF_SALT_BYTES);
            $nonce = \random_bytes(self::NONCE_BYTES);

            // We're going to use the provided key to derive separate encryption
            // and authentication keys using HKDF. This is now the recommended
            // practice to mitigate "invisible salamander" and "cross-protocol"
            // attacks affecting the newer AEAD constructions.  We'll use a salted
            // derivative of HKDF using Blake2b instead of a true HMAC to split the keys.
            $prk = Util::hash($key->bytes(), \str_repeat("\x00", SharedKey::LENGTH));

            // Create the encrypted message without an integral authentication tag
            // note that "\x01" is being used as a block id
            $encrypted_text = \sodium_crypto_stream_xchacha20_xor(
                $plaintext,
                $nonce,
                Util::hash(self::HKDF_SBOX_INFO . $hkdf_salt . "\x01", $prk),
            );

            // Calculate a 256-bit authentication tag, using Pre-Authentication Encoding
            // (PAE) to create the message to authenticate
            $authentication_tag = Util::hash(
                Util::pae($hkdf_salt, $nonce, $additional_data, $encrypted_text),
                Util::hash(self::HKDF_AUTH_INFO . $hkdf_salt . "\x01", $prk),
                self::AUTH_TAG_BYTES,
            );

            // Append the authentication tag to the ciphertext
            $ciphertext = $hkdf_salt . $nonce . $encrypted_text . $authentication_tag;

            return Util::encode($encoding, $ciphertext);
        } catch (\SodiumException $e) {
            throw new CryptoRuntimeException('Encryption Failed', 0, $e);
        } finally {
            // Zero out all sensitive data
            Util::memzero($prk, $hkdf_salt, $nonce, $encrypted_text, $authentication_tag);
        }
    }

    public static function decrypt(
        #[\SensitiveParameter] Key $key,
        #[\SensitiveParameter] string $ciphertext,
        #[\SensitiveParameter] string $additional_data = '',
        Encoding $encoding = Encoding::Base64,
    ): string {
        try {
            // Decode the ciphertext to a raw binary string and validate its length
            $ciphertext = Util::decode($encoding, $ciphertext);
            $message_length = \strlen($ciphertext);
            if ($message_length < self::MIN_CIPHERTEXT_BYTES) {
                throw new InvalidMessage('Message is Too Short');
            }

            // Unpack the rest of the ciphertext into the component parts
            $hkdf_salt = \substr($ciphertext, 0, self::HKDF_SALT_BYTES);
            $nonce = \substr($ciphertext, self::HKDF_SALT_BYTES, self::NONCE_BYTES);
            $encrypted_length = \strlen($ciphertext) - self::MIN_CIPHERTEXT_BYTES;
            $encrypted_text = \substr($ciphertext, self::MIN_CIPHERTEXT_BYTES - self::AUTH_TAG_BYTES, $encrypted_length);
            $authentication_tag = \substr($ciphertext, $message_length - self::AUTH_TAG_BYTES, self::AUTH_TAG_BYTES);

            // Generate the 256-bit pseudorandom key which will be used to
            // further derive separate authentication and encryption keys via
            // salted Blake2b HKDF with a fixed info parameter.
            $prk = Util::hash($key->bytes(), \str_repeat("\x00", SharedKey::LENGTH));

            // Verify the authentication tag before decrypting the message by
            // calculating the expected tag with the derived authentication key.
            $calculated_tag = Util::hash(
                Util::pae($hkdf_salt, $nonce, $additional_data, $encrypted_text),
                Util::hash(self::HKDF_AUTH_INFO . $hkdf_salt . "\x01", $prk),
                self::AUTH_TAG_BYTES,
            );

            // If the authentication tag is valid, decrypt the message, using
            // the encryption key derived from the PRK key. Note that we use
            // hash_equals() here for constant-time comparison.
            if (\hash_equals($calculated_tag, $authentication_tag)) {
                return \sodium_crypto_stream_xchacha20_xor(
                    $encrypted_text,
                    $nonce,
                    Util::hash(self::HKDF_SBOX_INFO . $hkdf_salt . "\x01", $prk),
                );
            }

            // Fail Closed. If the authentication tag is invalid, throw an exception.
            throw new InvalidMessage('Authentication Tag Could Not Be Verified');
        } catch (\SodiumException $e) {
            throw new CryptoRuntimeException('Decryption Failed', 0, $e);
        } finally {
            Util::memzero($prk, $calculated_tag, $hkdf_salt, $encrypted_text, $nonce);
        }
    }
}
