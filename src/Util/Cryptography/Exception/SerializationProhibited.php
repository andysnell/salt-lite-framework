<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Util\Cryptography\Exception;

class SerializationProhibited extends CryptoLogicException
{
    public function __construct()
    {
        parent::__construct('Serialization of Objects with Sensitive Parameters is Prohibited');
    }
}
