# Cryptography Utilities

# Symmetric Encryption and Decryption

`\PhoneBurner\SaltLite\Framework\Util\Crypto\Symmetric\Symmetric` provides a 
simple interface for symmetric encryption and decryption. Unlike the otherwise
excellent Halite library, the methods on this class are not static, allowing the
class to be injected into other classes and tested more easily.

The encryption algorithm is a AEAD construct, allowing the use of optional additional
data to bind the encrypted data to the context in which it was encrypted. The
underlying encryption algorithm is XChaCha20-Blake2b, utilizing split encryption
and authentication keys, which mitigates newer attacks on common AEAD algorithms 
like XChaCha20-Poly1305.

```php

use PhoneBurner\SaltLite\Framework\Util\Crypto\Encoding;
use PhoneBurner\SaltLite\Framework\Util\Crypto\Symmetric\Symmetric;
use PhoneBurner\SaltLite\Framework\Util\Crypto\Symmetric\SharedKey;

$key = SharedKey::generate();
$symmetric = new Symmetric();

// encrypting a message
$ciphertext = $symmetric->encrypt($key, 'message');

// authenticating and decrypting a message
$plaintext = $symmetric->decrypt($key, $ciphertext);

// encrypting a message with additional data
$ciphertext = $symmetric->encrypt($key, 'message', 'additional data');

// authenticating and decrypting a message with additional data
$plaintext = $symmetric->decrypt($key, $ciphertext, 'additional data');

// encrypting a message, returning raw binary data
$ciphertext = $symmetric->encrypt($key, 'message', encoding: Encoding::None)
```

