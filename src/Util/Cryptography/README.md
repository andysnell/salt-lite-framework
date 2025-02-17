# Cryptography Utilities

### Defaults
- Symmetric Encryption: XChaCha20+Blake2b (Split Encryption/Authentication Keys)
- Asymmetric Encryption: ECDH-X25519+XChaCha20+Blake2b
- Digital Signatures: Ed25519
- Key Derivation: HKDF-BLAKE2b
- Cryptographic Hashing: BLAKE2b-256
- Fast Hashing: XXH3
- Encoding: Base64URL

## TL;DR:

Use the `PhoneBurner\SaltLite\Framework\Util\Cryptography\Natrium` facade to 
access the cryptographic operations with the keys derived from the app key in
configuration.


### Symmetric Encryption and Decryption

```php
<?php 

use PhoneBurner\SaltLite\Framework\Util\Cryptography\String\ConstantTimeEncoding;
use PhoneBurner\SaltLite\Framework\Util\Cryptography\Symmetric\Symmetric;
use PhoneBurner\SaltLite\Framework\Util\Cryptography\Symmetric\SharedKey;
use PhoneBurner\SaltLite\Framework\Util\Cryptography\String\Ciphertext;
use PhoneBurner\SaltLite\Framework\Util\Cryptography\KeyManagement\KeyDerivation;

// Import a shared key from a hex/base64/base64url encoded string
$key = SharedKey::import('2UMc9-jHA_L0f5uimbHI6mDdnZFjy5nLB961cEPefY4=', ConstantTimeEncoding::Base64Url);

// Recommended: Derive a deterministic, context-specific sub-key from the shared key.
$key = KeyDerivation::shared($key, 'my-context-specific-info');

// Encrypt a message using the default algorithm (XChaCha20+Blake2b)
$plaintext = 'The quick brown fox jumps over the lazy dog';
$ciphertext = new Symmetric()->encrypt($key, $plaintext);

// Export the ciphertext to a base64url encoded string with constant time encoding
$encoded = $ciphertext->export(ConstantTimeEncoding::Base64Url); 

// Import a ciphertext from a hex/base64/base64url encoded string
$ciphertext = Ciphertext::import($encoded);

// Decrypt the ciphertext back into the original message
$plaintext = new Symmetric()->decrypt($key, $ciphertext); 

echo $plaintext; // "The quick brown fox jumps over the lazy dog"
```

### Authenticated Asymmetric Encryption and Decryption

```php
<?php 

use PhoneBurner\SaltLite\Framework\Util\Cryptography\String\ConstantTimeEncoding;
use PhoneBurner\SaltLite\Framework\Util\Cryptography\String\Ciphertext;
use PhoneBurner\SaltLite\Framework\Util\Cryptography\Asymmetric\EncryptionKeyPair;
use PhoneBurner\SaltLite\Framework\Util\Cryptography\Asymmetric\EncryptionPublicKey;
use PhoneBurner\SaltLite\Framework\Util\Cryptography\Asymmetric\Asymmetric;

// Acting as the Sender:

// Import your secret/public keypair key from a hex/base64/base64url encoded string
$key_pair = EncryptionKeyPair::import(<<<'EOF'
beKTtL77ap8MoffLjjadjA-k8HFEgqQcrW3iu9Qqpa_S5QJo3L6L6awQJVXfLELYIqRAQ6ARMEOMxD6jcKUZaA==
EOF, ConstantTimeEncoding::Base64Url);

// Make sure the recipient knows your public key, which you can export from your keypair:
$key_pair->public()->export(ConstantTimeEncoding::Base64Url);

// Import the public key of the recipient from a hex/base64/base64url encoded string
$public_key = EncryptionPublicKey::import('goj3Wdi82RvYKX4DZmcrB8jes2M8KgXyINaeH6GiAQE=');

// Encrypt a message using the default algorithm (X25519+XChaCha20+Blake2b)
$plaintext = 'The quick brown fox jumps over the lazy dog';
$ciphertext = new Asymmetric()->encrypt($key_pair, $public_key, $plaintext);

// Export the ciphertext to a base64url encoded string with constant time encoding
$encoded = $ciphertext->export(ConstantTimeEncoding::Base64Url); 

// Acting as the Recipient:

// Import your secret/public keypair key from a hex/base64/base64url encoded string
$key_pair = EncryptionKeyPair::import(<<<'EOF'
tuzZL5sWDqS2jflaSH6KjdEXiHFs245_KVtlS56KBqSCiPdZ2LzZG9gpfgNmZysHyN6zYzwqBfIg1p4foaIBAQ==
EOF, ConstantTimeEncoding::Base64Url);

// Import the public key of the sender from a hex/base64/base64url encoded string
$public_key = EncryptionPublicKey::import('0uUCaNy-i-msECVV3yxC2CKkQEOgETBDjMQ-o3ClGWg=');

// Import a ciphertext from a hex/base64/base64url encoded string
$ciphertext = Ciphertext::import($encoded);

// Decrypt the ciphertext back into the original message
$plaintext = new Asymmetric()->decrypt($key_pair, $public_key, $ciphertext); 

echo $plaintext; // "The quick brown fox jumps over the lazy dog"
```


### Key Derivation (HKDF)

A key derivation function (KDF) is used to derive keys from a master key, usually
for a specific purpose or context.


### Supported Cryptographic Primitives

#### Symmetric Encryption/Decryption
- AEGIS-256
- XChaCha20+Blake2b (with Split Encryption/Authentication Keys)
- XChaCha20+Poly1305 IEFT
- AES-256-GCM
- XSalsa20+Poly1305

#### Symmetric Authentication
- BLAKE2b with 512-bit digest

#### Supported Authenticated Asymmetric Encryption/Decryption
- X25519+AEGIS-256
- X25519+XChaCha20+Blake2b
- X25519+XChaCha20+Poly1305 IEFT
- X25519+AES-256-GCM
- X25519+XSalsa20+Poly1305

#### Anonymous Asymmetric Encryption/Decryption
- X25519+AEGIS-256
- X25519+XChaCha20+Blake2b
- X25519+XChaCha20+Poly1305 IEFT
- X25519+XSalsa20+Poly1305

#### Public Key Digital Signatures
- Ed25519