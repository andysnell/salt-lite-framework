<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Util\Cryptography\Attribute;

use PhoneBurner\SaltLite\Framework\Util\Cryptography\Asymmetric\EncryptionAlgorithm as AsymmetricEncryptionAlgorithm;
use PhoneBurner\SaltLite\Framework\Util\Cryptography\Symmetric\EncryptionAlgorithm as SymmetricEncryptionAlgorithm;

#[\Attribute]
final readonly class AlgorithmImplementation
{
    public function __construct(public AsymmetricEncryptionAlgorithm|SymmetricEncryptionAlgorithm $algorithm)
    {
    }
}
