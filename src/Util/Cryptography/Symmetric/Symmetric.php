<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Util\Cryptography\Symmetric;

use PhoneBurner\SaltLite\Framework\Util\Attribute\Contract;
use PhoneBurner\SaltLite\Framework\Util\Cryptography\String\BinaryString;
use PhoneBurner\SaltLite\Framework\Util\Cryptography\String\Ciphertext;
use PhoneBurner\SaltLite\Framework\Util\Cryptography\String\MessageSignature;
use PhoneBurner\SaltLite\Framework\Util\Cryptography\Symmetric\Algorithm\XChaCha20Blake2b;

#[Contract]
class Symmetric
{
    public const int MIN_CIPHERTEXT_BYTES = XChaCha20Blake2b::MIN_CIPHERTEXT_BYTES;

    public function encrypt(
        #[\SensitiveParameter] SharedKey $key,
        #[\SensitiveParameter] \Stringable|string $plaintext,
        #[\SensitiveParameter] \Stringable|string $additional_data = '',
        Algorithm $algorithm = Algorithm::XChaCha20Blake2b,
    ): Ciphertext {
        return $algorithm->implementation()::encrypt(
            $key,
            $plaintext instanceof BinaryString ? $plaintext->bytes() : (string)$plaintext,
            (string)$additional_data,
        );
    }

    /**
     * @return string|null We do not want to treat a failed decryption as an
     * exceptional condition, and given the advancements in making PHP type-safe
     * the risk of accidentally using a null value as a string should be minimal,
     * as long as the caller is aware of the possibility and handles it correctly.
     * The underlying sodium_crypto_*_decrypt() functions return false on failure;
     * however, given the null-specific operators, it is a much easier to deal with
     * a null than a boolean false, when an empty string _could_ be a valid result.
     */
    public function decrypt(
        #[\SensitiveParameter] SharedKey $key,
        #[\SensitiveParameter] Ciphertext $ciphertext,
        #[\SensitiveParameter] \Stringable|string $additional_data = '',
        Algorithm $algorithm = Algorithm::XChaCha20Blake2b,
    ): string|null {
        return $algorithm->implementation()::decrypt(
            $key,
            $ciphertext,
            (string)$additional_data,
        );
    }

    /**
     * Compute a detached message authentication code (MAC) for a given message,
     * with the given shared key. Only parties with the shared key can verify the
     * authenticity of the message. Since the message remains in plaintext, this
     * provides assurance of message integrity, but not confidentiality.
     *
     * Uses the BLAKE2b MAC construction.
     *
     * Note: The \sodium_crypto_auth method uses HMAC-SHA512/256, or rather,
     * HMAC-SHA512 truncated to 256 bits, which is different from and incompatible
     * with the "FIPS" definition of HMAC-SHA512/256. That version is used to
     * produce the output of \hash_hmac('sha512/256', $message, $key).
     */
    public function sign(
        #[\SensitiveParameter] SharedKey $key,
        #[\SensitiveParameter] \Stringable|string $plaintext,
    ): MessageSignature {
        return new MessageSignature(\sodium_crypto_generichash(
            $plaintext instanceof BinaryString ? $plaintext->bytes() : (string)$plaintext,
            $key->bytes(),
            MessageSignature::LENGTH,
        ));
    }

    /**
     * Verify the authenticity of a message with a given detached message
     * authentication code (MAC) produced with the sign() method.
     */
    public function verify(
        #[\SensitiveParameter] SharedKey $key,
        #[\SensitiveParameter] MessageSignature $signature,
        #[\SensitiveParameter] \Stringable|string $plaintext,
    ): bool {
        // Calculate the MAC of the message with the shared key.
        $authentication_tag = \sodium_crypto_generichash(
            $plaintext instanceof BinaryString ? $plaintext->bytes() : (string)$plaintext,
            $key->bytes(),
            MessageSignature::LENGTH,
        );

        // Compare the calculated MAC value to the provided MAC value in constant time.
        $result = \hash_equals($authentication_tag, $signature->bytes());

        // Clear the calculated MAC from memory.
        \sodium_memzero($authentication_tag);

        return $result;
    }
}
