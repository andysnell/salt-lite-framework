<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Util\Cryptography\Asymmetric;

use PhoneBurner\SaltLite\Framework\Util\Cryptography\Symmetric\SharedKey;

/**
 * The ECDH+Blake2b key exchange algorithm supports the creation of four distinct
 * shared keys, based on which party is the "server" and which is the "client",
 * and whether the keys are used for transmitting or receiving information from
 * the other party. The client/server roles are arbitrary and can be reversed,
 * as long as both parties agree on which role they are playing, this makes
 * the key exchange algorithm suitable for use in a wide variety of scenarios,
 * especially for back-and-forth communication between two parties.
 *
 * For our more typical use cases, we only care about the encrypting party computing
 * the same shared secret as the decrypting party, without concern for who is
 * acting in what role. We expose a simplified API for this use case, where only
 * with the `encryption` and `decryption` methods, which still allow for distinct
 * directional keys, but do not require the caller to specify the client/server roles.
 */
final readonly class KeyExchange
{
    private function __construct(
        public SharedKey $decryption_key,
        public SharedKey $encryption_key,
    ) {
    }

    public static function encryption(EncryptionKeyPair $key_pair, EncryptionPublicKey $public_key): SharedKey
    {
        return self::server($key_pair, $public_key)->encryption_key;
    }

    public static function decryption(EncryptionKeyPair $key_pair, EncryptionPublicKey $public_key): SharedKey
    {
        return self::client($key_pair, $public_key)->decryption_key;
    }

    public static function server(EncryptionKeyPair $key_pair, EncryptionPublicKey $public_key): self
    {
        [$decryption, $encryption] = \sodium_crypto_kx_server_session_keys($key_pair->bytes(), $public_key->bytes());
        return new self(new SharedKey($decryption), new SharedKey($encryption));
    }

    public static function client(EncryptionKeyPair $key_pair, EncryptionPublicKey $public_key): self
    {
        [$decryption, $encryption] = \sodium_crypto_kx_client_session_keys($key_pair->bytes(), $public_key->bytes());
        return new self(new SharedKey($decryption), new SharedKey($encryption));
    }
}
