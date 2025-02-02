<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Tests\Util\Crypto\Symmetric;

use PhoneBurner\SaltLite\Framework\Util\Crypto\Encoding;
use PhoneBurner\SaltLite\Framework\Util\Crypto\Exception\InvalidMessage;
use PhoneBurner\SaltLite\Framework\Util\Crypto\Symmetric\SharedKey;
use PhoneBurner\SaltLite\Framework\Util\Crypto\Symmetric\Symmetric;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class SymmetricTest extends TestCase
{
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
    #[DataProvider('providesPlaintextTestCases')]
    public function encrypt_and_decrypt_work_without_additional_data(string $plaintext): void
    {
        $key = SharedKey::generate();
        $ciphertext = new Symmetric()->encrypt($key, $plaintext);
        $decrypted = new Symmetric()->decrypt($key, $ciphertext);

        self::assertMatchesRegularExpression('/^[A-Za-z0-9+\/]+={0,2}$/', $ciphertext);
        self::assertSame($plaintext, $decrypted);
    }

    #[Test]
    #[DataProvider('providesPlaintextTestCases')]
    public function encrypt_and_decrypt_work_with_additional_data(string $plaintext): void
    {
        $key = SharedKey::generate();
        $ciphertext = new Symmetric()->encrypt($key, $plaintext, 'additional data');
        $decrypted = new Symmetric()->decrypt($key, $ciphertext, 'additional data');

        self::assertMatchesRegularExpression('/^[A-Za-z0-9+\/]+={0,2}$/', $ciphertext);
        self::assertSame($plaintext, $decrypted);
    }

    #[Test]
    #[DataProvider('providesEncodingTestCases')]
    public function encrypt_and_decrypt_work_with_different_encodings(
        string $plaintext,
        Encoding $encoding,
        string $regex,
    ): void {
        $key = SharedKey::generate();
        $ciphertext = new Symmetric()->encrypt($key, $plaintext, encoding: $encoding);
        $decrypted = new Symmetric()->decrypt($key, $ciphertext, encoding: $encoding);
        self::assertMatchesRegularExpression($regex, $ciphertext);
        self::assertSame($plaintext, $decrypted);
    }

    #[Test]
    #[DataProvider('providesPlaintextTestCases')]
    public function message_length_is_checked_(string $plaintext): void
    {
        $key = SharedKey::generate();
        $ciphertext = new Symmetric()->encrypt($key, $plaintext, encoding: Encoding::None);

        // cut off the last bytes, making it too short.
        $ciphertext = \substr($ciphertext, 0, Symmetric::MIN_CIPHERTEXT_BYTES - 1);

        $this->expectException(InvalidMessage::class);
        $this->expectExceptionMessage('Message is Too Short');
        new Symmetric()->decrypt($key, $ciphertext, encoding: Encoding::None);
    }

    #[Test]
    #[DataProvider('providesPlaintextTestCases')]
    public function the_version_header_is_checked_(string $plaintext): void
    {
        $key = SharedKey::generate();
        $ciphertext = new Symmetric()->encrypt($key, $plaintext, encoding: Encoding::None);

        // change one byte in the version header (e.g. v1 to v2)
        $ciphertext[3] = '2';

        $this->expectException(InvalidMessage::class);
        $this->expectExceptionMessage('Message Has Invalid Version Header');
        new Symmetric()->decrypt($key, $ciphertext, encoding: Encoding::None);
    }

    #[Test]
    #[DataProvider('providesPlaintextTestCases')]
    public function message_authentication_works(string $plaintext): void
    {
        $key = SharedKey::generate();
        $ciphertext = new Symmetric()->encrypt($key, $plaintext, encoding: Encoding::None);

        // change one byte in the authentication tag
        $length = \strlen($ciphertext);
        $ciphertext[$length - 4] = $ciphertext[$length - 4] === 'a' ? 'b' : 'a';

        $this->expectException(InvalidMessage::class);
        $this->expectExceptionMessage('Authentication Tag Could Not Be Verified');
        new Symmetric()->decrypt($key, $ciphertext, encoding: Encoding::None);
    }

    public static function providesPlaintextTestCases(): iterable
    {
        yield 'HelloWorld' => ['Hello World'];
        yield 'EmptyString' => [''];
        yield 'LoremIpsum' => [self::LOREM_IPSUM_PLAINTEXT];
    }

    public static function providesEncodingTestCases(): iterable
    {
        foreach (self::providesPlaintextTestCases() as $name => [$plaintext]) {
            yield $name . '_None' => [$plaintext, Encoding::None, '/^[\x00-\xFF]+$/'];
            yield $name . '_Hex' => [$plaintext, Encoding::Hex, '/^[0-9a-f]+$/'];
            yield $name . '_Base64' => [$plaintext, Encoding::Base64, '/^[A-Za-z0-9+\/]+={0,2}$/'];
            yield $name . '_Base64NoPadding' => [$plaintext, Encoding::Base64NoPadding, '/^[A-Za-z0-9+\/]+$/'];
            yield $name . '_Base64Url' => [$plaintext, Encoding::Base64Url, '/^[A-Za-z0-9-_]+={0,2}$/'];
            yield $name . '_Base64UrlNoPadding' => [$plaintext, Encoding::Base64UrlNoPadding, '/^[A-Za-z0-9-_]+$/'];
        }
    }
}
