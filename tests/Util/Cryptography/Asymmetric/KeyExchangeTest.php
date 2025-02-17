<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Tests\Util\Cryptography\Asymmetric;

use PhoneBurner\SaltLite\Framework\Util\Cryptography\Asymmetric\EncryptionKeyPair;
use PhoneBurner\SaltLite\Framework\Util\Cryptography\Asymmetric\KeyExchange;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class KeyExchangeTest extends TestCase
{
    #[Test]
    public function happy_path_for_simplified_bidirectional_key_exchange(): void
    {
        // Alice and Bob generate their key pairs and exchange public keys
        $alice_key_pair = EncryptionKeyPair::generate();
        $bob_key_pair = EncryptionKeyPair::generate();

        // Alice and Bob can derive shared keys for encrypting and decrypting messages
        // between them, using the public key of the other party and their own private key.
        // This works because the computed encryption and decryption keys are the same for both parties.
        $alice_encryption_key = KeyExchange::encryption($alice_key_pair, $bob_key_pair->public());
        $bob_decryption_key = KeyExchange::decryption($bob_key_pair, $alice_key_pair->public());
        self::assertSame($alice_encryption_key->bytes(), $bob_decryption_key->bytes());

        // Bob can also derive an encryption key for sending messages to Alice.
        // This key is different from the encryption key that Alice used to send
        // messages to Bob. Using this method of key exchange, there are two keys;
        $bob_encryption_key = KeyExchange::encryption($bob_key_pair, $alice_key_pair->public());
        $alice_decryption_key = KeyExchange::decryption($alice_key_pair, $bob_key_pair->public());
        self::assertSame($bob_encryption_key->bytes(), $alice_decryption_key->bytes());
        self::assertNotSame($alice_encryption_key->bytes(), $bob_encryption_key->bytes());
        self::assertNotSame($alice_decryption_key->bytes(), $bob_decryption_key->bytes());
    }

    #[Test]
    public function happy_path_for_server_and_client_bidirectional_key_exchange(): void
    {
        $server_key_pair = EncryptionKeyPair::generate();
        $client_key_pair = EncryptionKeyPair::generate();

        $server = KeyExchange::server($server_key_pair, $client_key_pair->public());
        $client = KeyExchange::client($client_key_pair, $server_key_pair->public());

        // the key exchange produces two different keys, one for encryption and one for decryption
        self::assertNotSame($server->encryption_key->bytes(), $server->decryption_key->bytes());
        self::assertNotSame($client->encryption_key->bytes(), $client->decryption_key->bytes());

        // the server's encryption key should be the same as the client's decryption key
        // the server's decryption key should be the same as the client's encryption key
        self::assertSame($server->encryption_key->bytes(), $client->decryption_key->bytes());
        self::assertSame($server->decryption_key->bytes(), $client->encryption_key->bytes());

        // reversing the server/client roles results in different shared secrets,
        // however, the encryption key of one party is the decryption key of the
        // other party and vice versa. That is, the server/client roles are arbitrary,
        // as long as they are consistent.
        $rev_server = KeyExchange::client($server_key_pair, $client_key_pair->public());
        $rev_client = KeyExchange::server($client_key_pair, $server_key_pair->public());

        // the key exchange produces two different keys, one for encryption and one for decryption
        self::assertNotSame($rev_server->encryption_key->bytes(), $rev_server->decryption_key->bytes());
        self::assertNotSame($rev_client->encryption_key->bytes(), $rev_client->decryption_key->bytes());

        // the server's encryption key should be the same as the client's decryption key
        // the server's decryption key should be the same as the client's encryption key
        self::assertSame($rev_server->encryption_key->bytes(), $rev_client->decryption_key->bytes());
        self::assertSame($rev_server->decryption_key->bytes(), $rev_client->encryption_key->bytes());

        // The keys should be different from the original exchange, note that we
        // only need to check one role's keys here, as we already asserted that the
        // opposite client keys are the same, e.g. $server->encryption_key === $client->decryption_key
        self::assertNotSame($server->encryption_key->bytes(), $rev_server->encryption_key->bytes());
        self::assertNotSame($server->encryption_key->bytes(), $rev_server->decryption_key->bytes());
    }
}
