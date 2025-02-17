<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Tests\Util\Cryptography\Asymmetric;

use PhoneBurner\SaltLite\Framework\Util\Cryptography\Asymmetric\Asymmetric;
use PhoneBurner\SaltLite\Framework\Util\Cryptography\Asymmetric\EncryptionKeyPair;
use PhoneBurner\SaltLite\Framework\Util\Cryptography\Asymmetric\SignatureKeyPair;
use PhoneBurner\SaltLite\Framework\Util\Cryptography\String\MessageSignature;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class AsymmetricTest extends TestCase
{
    public const string KNOWN_SENDER_ENCRYPTION_KEYPAIR = 'kk72c6s2di5fKvBXLSbYCISOvj+a26p3nhe/+TzTi3osLpeqgv2ChN/RzsZskMYLU7jct02PprzdoHPeUwt5Kg==';

    public const string KNOWN_RECIPIENT_ENCRYPTION_KEYPAIR = 'fvVzvZ085EQ+chb5HtMzBhLcBHjVAQi1g4CnQfuJnjTGPBGm6sIenWqy7v7b4iNdaQhtpn6gDVtpXquKyo7KKQ==';

    public const string KNOWN_SIGNATURE_KEYPAIR = 'idOxepSuhF59BDvrimjszqDXrtdtBIgcLmTJRUQpbWHIvFyDdNItbTmkZW2fm2NSFQf-pLwzmSmX6G8Ot46VfMi8XIN00i1tOaRlbZ-bY1IVB_6kvDOZKZfobw63jpV8';

    public const string KNOWN_MESSAGE_SIGNATURE = 'hQr_LHoLyCc_d8RqB-gzybe0ayflIRckLYGagrck1wsjND-YTObh_-6yHs3H8wgh7WivJ0SO50KhHz2y7A2bBA';

    private const string LOREM_IPSUM_PLAINTEXT = <<<'EOL'
        Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec vitae 
        nunc eu sem laoreet posuere. Praesent elementum quam ac diam rhoncus 
        mattis. Sed nulla nibh, mattis quis leo sed, vulputate rutrum sem. 
        Nulla quis fringilla mauris, sit amet malesuada lectus. Etiam vel 
        egestas ipsum. Curabitur aliquet blandit mi sed facilisis. Mauris sit 
        amet venenatis massa, vitae mattis eros. Duis porttitor elit ut massa 
        feugiat, non pharetra turpis suscipit. Etiam ut fringilla lacus. Aliquam
        bibendum, quam vel imperdiet ornare, erat lectus ultricies tortor, sit 
        amet sollicitudin neque justo et urna. Etiam feugiat, ligula a 
        sollicitudin efficitur, enim velit vulputate orci, in dapibus massa 
        orci non augue. Aenean leo nisl, tincidunt sit amet nunc fringilla, 
        posuere sagittis turpis.
        EOL;

    #[Test]
    public function encryption_happy_path(): void
    {
        $sender_keypair = EncryptionKeyPair::generate();
        $recipient_keypair = EncryptionKeyPair::generate();

        $ciphertext = new Asymmetric()->encrypt($sender_keypair, $recipient_keypair->public, self::LOREM_IPSUM_PLAINTEXT);
        $plaintext = new Asymmetric()->decrypt($recipient_keypair, $sender_keypair->public, $ciphertext);

        self::assertSame(self::LOREM_IPSUM_PLAINTEXT, $plaintext);
    }

    #[Test]
    public function sign_and_verify_happy_path(): void
    {
        $key_pair = SignatureKeyPair::generate();

        $message_signature = new Asymmetric()->sign($key_pair, self::LOREM_IPSUM_PLAINTEXT);

        self::assertTrue(new Asymmetric()->verify($key_pair->public, $message_signature, self::LOREM_IPSUM_PLAINTEXT));
    }

    #[Test]
    public function sign_and_verify_regression_test(): void
    {
        $key_pair = SignatureKeyPair::import(self::KNOWN_SIGNATURE_KEYPAIR);
        $message_signature = MessageSignature::import(self::KNOWN_MESSAGE_SIGNATURE);

        self::assertTrue(new Asymmetric()->verify($key_pair->public, $message_signature, self::LOREM_IPSUM_PLAINTEXT));
    }
}
