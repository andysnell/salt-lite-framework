<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Util\Cryptography\KeyManagement;

use PhoneBurner\SaltLite\Framework\Util\Collections\Map\GenericMapCollection;
use PhoneBurner\SaltLite\Framework\Util\Cryptography\Asymmetric\EncryptionKeyPair;
use PhoneBurner\SaltLite\Framework\Util\Cryptography\Asymmetric\SignatureKeyPair;
use PhoneBurner\SaltLite\Framework\Util\Cryptography\KeyManagement\KeyDerivation;
use PhoneBurner\SaltLite\Framework\Util\Cryptography\Symmetric\SharedKey;

/**
 * Keeps track of keys derived from the app key, for use in encryption and decryption.
 * As a best practice, we want to use a unique key for each type of cryptographic
 * operation, so that a compromise of one key does not compromise all of them.
 *
 * While we need to manage many shared keys, we want to limit the number of asymmetric
 * key pairs used, since the public key is shared with other parties. We derive
 * one X25519 key pair for encryption and one Ed25519 key pair for signatures
 * using a HKDF-Blake2b derivation of the 256-bit app key as the seed.
 *
 * Note: All derived keys can be cleared by calling the clear() method.
 *
 * @extends GenericMapCollection<SharedKey>
 */
final class KeyChain extends GenericMapCollection
{
    private EncryptionKeyPair|null $encryption_key_pair = null;

    private SignatureKeyPair|null $signature_key_pair = null;

    public function __construct(public readonly SharedKey $app_key)
    {
    }

    public function shared(string|null $context = null): SharedKey
    {
        return $context === null
            ? $this->app_key
            : $this->remember($context, fn(): SharedKey => KeyDerivation::shared($this->app_key, $context));
    }

    public function encryption(): EncryptionKeyPair
    {
        return $this->encryption_key_pair ??= KeyDerivation::encryption($this->app_key);
    }

    public function signature(): SignatureKeyPair
    {
        return $this->signature_key_pair ??= KeyDerivation::signature($this->app_key);
    }

    #[\Override]
    public function clear(): void
    {
        $this->encryption_key_pair = null;
        $this->signature_key_pair = null;
        parent::clear();
    }
}
