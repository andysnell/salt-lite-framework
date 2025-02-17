<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Tests\Util\Cryptography\Hash;

use PhoneBurner\SaltLite\Framework\Util\Cryptography\Exception\InvalidHash;
use PhoneBurner\SaltLite\Framework\Util\Cryptography\Hash\HashAlgorithm;
use PhoneBurner\SaltLite\Framework\Util\Cryptography\Hash\Hmac;
use PhoneBurner\SaltLite\Framework\Util\Cryptography\String\ConstantTimeEncoding;
use PhoneBurner\SaltLite\Framework\Util\Cryptography\Symmetric\SharedKey;
use PhoneBurner\SaltLite\Framework\Util\Filesystem\FileReader;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use const PhoneBurner\SaltLite\Framework\UNIT_TEST_ROOT;

final class HmacTest extends TestCase
{
    private const string HMAC_KEY = '8df08d18de44263419a39074956dc2ef8a3a0b1b26db984282bfcfc202cda41d';

    #[DataProvider('providesStringsWithValidHashFormats')]
    #[Test]
    public function make_returns_hmac_from_hashed_string_and_algorithm(
        HashAlgorithm $algorithm,
        string $digest,
    ): void {
        $hmac = Hmac::make($digest, $algorithm);

        self::assertSame(\strtolower($digest), $hmac->digest());
        self::assertSame(\strtolower($digest), (string)$hmac);
        self::assertSame($algorithm, $hmac->algorithm);
        self::assertSame($algorithm, $hmac->algorithm());
    }

    #[DataProvider('providesStringsWithInvalidHashFormats')]
    #[Test]
    public function make_throws_exception_on_invalid_hash_string(
        HashAlgorithm $algorithm,
        string $invalid_hash,
    ): void {
        $this->expectException(InvalidHash::class);
        Hmac::make($invalid_hash, $algorithm);
    }

    #[DataProvider('providesStringTestCases')]
    #[Test]
    public function string_hmacs_an_arbitrary_string_with_algorithm(
        HashAlgorithm $algorithm,
        string $digest,
    ): void {
        $key = SharedKey::import(self::HMAC_KEY, ConstantTimeEncoding::Hex);
        $hmac = Hmac::string('foo bar baz', $key, $algorithm);

        self::assertSame($digest, $hmac->digest());
        self::assertSame($digest, (string)$hmac);
        self::assertSame($algorithm, $hmac->algorithm);
        self::assertSame($algorithm, $hmac->algorithm());
    }

    #[DataProvider('providesFileTestCases')]
    #[Test]
    public function file_hmacs_an_arbitrary_file_with_algorithm(array $test_case): void
    {
        $hmac = Hmac::file(
            $test_case['file'],
            SharedKey::import(self::HMAC_KEY, ConstantTimeEncoding::Hex),
            $test_case['algorithm'],
        );

        self::assertSame($test_case['digest'], $hmac->digest());
        self::assertSame($test_case['digest'], (string)$hmac);
        self::assertSame($test_case['algorithm'], $hmac->algorithm);
        self::assertSame($test_case['algorithm'], $hmac->algorithm());
    }

    #[DataProvider('providesFileTestCases')]
    #[Test]
    public function iterable_hmacs_an_arbitrary_pump_iterator_with_algorithm(array $test_case): void
    {
        $hmac = Hmac::iterable(
            FileReader::make($test_case['file']),
            SharedKey::import(self::HMAC_KEY, ConstantTimeEncoding::Hex),
            $test_case['algorithm'],
        );

        self::assertSame($test_case['digest'], $hmac->digest());
        self::assertSame($test_case['digest'], (string)$hmac);
        self::assertSame($test_case['algorithm'], $hmac->algorithm);
        self::assertSame($test_case['algorithm'], $hmac->algorithm());
    }

    #[DataProvider('providesFileTestCases')]
    #[Test]
    public function iterable_hashes_an_arbitrary_pump_iterator_recursively_with_algorithm(array $test_case): void
    {
        $hmac = Hmac::iterable([
            FileReader::make($test_case['file']),
        ], SharedKey::import(self::HMAC_KEY, ConstantTimeEncoding::Hex), $test_case['algorithm']);

        self::assertSame($test_case['digest'], $hmac->digest());
        self::assertSame($test_case['digest'], (string)$hmac);
        self::assertSame($test_case['algorithm'], $hmac->algorithm);
        self::assertSame($test_case['algorithm'], $hmac->algorithm());

        $hmac = Hmac::iterable([
            FileReader::make($test_case['file']),
            FileReader::make($test_case['file']),
            FileReader::make($test_case['file']),
        ], SharedKey::import(self::HMAC_KEY, ConstantTimeEncoding::Hex), $test_case['algorithm']);

        self::assertSame($test_case['digest-x3'], $hmac->digest());
        self::assertSame($test_case['digest-x3'], (string)$hmac);
        self::assertSame($test_case['algorithm'], $hmac->algorithm);
        self::assertSame($test_case['algorithm'], $hmac->algorithm());
    }

    #[Test]
    public function is_returns_true_if_two_hmacs_are_the_same_string_and_algorithm(): void
    {
        $key_0 = SharedKey::generate();
        $key_1 = SharedKey::generate();

        $hmac_00 = Hmac::string('foo bar baz', $key_0, HashAlgorithm::BLAKE2B);
        $hmac_10 = Hmac::string('foo bar baz', $key_0, HashAlgorithm::BLAKE2B);
        $hmac_20 = Hmac::string('wrong string', $key_0, HashAlgorithm::BLAKE2B);
        $hmac_30 = Hmac::string('foo bar baz', $key_0, HashAlgorithm::SHA256);
        $hmac_40 = Hmac::string('foo bar baz', $key_0, HashAlgorithm::SHA256);
        $hmac_50 = Hmac::string('wrong string', $key_0, HashAlgorithm::SHA256);

        $hmac_01 = Hmac::string('foo bar baz', $key_1, HashAlgorithm::BLAKE2B);
        $hmac_11 = Hmac::string('foo bar baz', $key_1, HashAlgorithm::BLAKE2B);
        $hmac_21 = Hmac::string('wrong string', $key_1, HashAlgorithm::BLAKE2B);
        $hmac_31 = Hmac::string('foo bar baz', $key_1, HashAlgorithm::SHA256);
        $hmac_41 = Hmac::string('foo bar baz', $key_1, HashAlgorithm::SHA256);
        $hmac_51 = Hmac::string('wrong string', $key_1, HashAlgorithm::SHA256);

        foreach (\range(0, 5) as $i) {
            foreach ([0, 1] as $j) {
                $hmac = 'hmac_' . $i . $j;
                self::assertTrue(${$hmac}->is(${$hmac}));
            }
        }

        self::assertTrue($hmac_00->is($hmac_10));
        self::assertFalse($hmac_00->is($hmac_20));
        self::assertFalse($hmac_00->is($hmac_30));
        self::assertTrue($hmac_30->is($hmac_40));
        self::assertFalse($hmac_30->is($hmac_50));

        self::assertTrue($hmac_01->is($hmac_11));
        self::assertFalse($hmac_01->is($hmac_21));
        self::assertFalse($hmac_01->is($hmac_31));
        self::assertTrue($hmac_31->is($hmac_41));
        self::assertFalse($hmac_31->is($hmac_51));

        self::assertFalse($hmac_00->is($hmac_01));
        self::assertFalse($hmac_10->is($hmac_11));
        self::assertFalse($hmac_20->is($hmac_21));
        self::assertFalse($hmac_30->is($hmac_31));
        self::assertFalse($hmac_40->is($hmac_41));
        self::assertFalse($hmac_50->is($hmac_51));
    }

    public static function providesStringsWithValidHashFormats(): \Generator
    {
        yield [HashAlgorithm::SHA512, '36d457859da599dd5d91c62f3879bb9a29374e75441b7d33343a19d5db39306d36d457859da599dd5d91c62f3879bb9a29374e75441b7d33343a19d5db39306d'];
        foreach ([HashAlgorithm::BLAKE2B, HashAlgorithm::SHA256, HashAlgorithm::SHA512_256_FIPS] as $algo) {
            yield [$algo, '36d457859da599dd5d91c62f3879bb9a29374e75441b7d33343a19d5db39306d'];
            yield [$algo, 'c168b0203132b736b5597b3a21cf52eabed4d99462d55ef8de38875ecd57bee4'];
            yield [$algo, 'e02fcdb4783c1d4fdb5a6e4422d24f88f21d38d5cd923c3b3839cbc888eff943'];
            yield [$algo, 'e23b10aa781610fd5fa64f195da052007d358c6085900288b82739b6c9a5a5d9'];
            yield [$algo, '98937a8700a31f85a37bb94370a88d519728805b5d8df2dd41275f27272b8d63'];
            yield [$algo, 'd2cd29f9a5c39df831d43539305dc208fdd192d5e16d1bb9416ed45f6401e592'];
            yield [$algo, '36D457859da599dd5d91c62F3879bB9a29374e75441b7d33343a19d5DB39306d'];
        }
    }

    public static function providesStringsWithInvalidHashFormats(): \Generator
    {
        foreach ([HashAlgorithm::BLAKE2B, HashAlgorithm::SHA256, HashAlgorithm::SHA512_256_FIPS] as $algo) {
            yield $algo->value . '_totally wrong' => [$algo, 'Hello, World'];
            yield $algo->value . '_empty_string' => [$algo, ''];
            yield $algo->value . '_right_length_wrong_chars' => [$algo, 'mmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmm'];
            yield $algo->value . '_one_invalid_char' => [$algo, '36d457859da599dd5d91c62f38P9bb9a29374e75441b7d33343a19d5db39306d'];
            yield $algo->value . '_one_char_too_many' => [$algo, '36d457859da599dd5d91c62K3879bb9a29374e75441b7d33343a19d5db39306da'];
            yield $algo->value . '_one_char_too_few' => [$algo, '36d457859da599dd5d91c62K3879bb9a29374e75441b7d33343a19d5db39306'];
            yield $algo->value . '_valid_hash_for_different_algo' => [$algo, '8843d7f92416211de9ebb963ff4ce28125932878'];
        }
        yield HashAlgorithm::MD5->value => [HashAlgorithm::MD5, '9A84680CC71CDA40F4EFA870DD6C589F'];
        yield HashAlgorithm::SHA1->value => [HashAlgorithm::SHA1, "5b27f55ed926c0e1b7c55368c690785b6552fe0e"];
        yield HashAlgorithm::XXH3->value => [HashAlgorithm::XXH3, '0944ba70db4a380a'];
    }

    public static function providesStringTestCases(): \Generator
    {
        yield [HashAlgorithm::BLAKE2B, '93597421ac1a305165260b51a3a09d6e5f71c0453daab8af64f53c60519dd8b0'];
        yield [HashAlgorithm::SHA3_256, 'b68ca8378e20be75fe8b403f6c97dbafb48c0c50586687dde5ab2d455845b0d7'];
        yield [HashAlgorithm::SHA256, '55aa176a571dcfbc6d7ea83f219fbfe57ccf0ec16d379a18acef886c93b4e5c5'];
    }

    public static function providesFileTestCases(): \Generator
    {
        yield [[
            'algorithm' => HashAlgorithm::BLAKE2B,
            'file' => UNIT_TEST_ROOT . '/Fixtures/lorem.txt',
            'digest' => 'b4c4ae4d307035d3ffa5f589aa6af3e4e0bfb4b7788fd70d0c720154ea965806',
            'digest-x3' => 'ab4b153c360326e00fee22029868e65430511d33e1435550a4e264c174f6d755',
        ]];

        yield [[
            'algorithm' => HashAlgorithm::SHA3_256,
            'file' => UNIT_TEST_ROOT . '/Fixtures/lorem.txt',
            'digest' => '8c475b6e7814eeafd30d7c3aeb66c87f6179c3db6f07766280c3d7480b5e8eb0',
            'digest-x3' => '533fbbe377de703a88c94d5f2a52ca8a6e2d552d24b5bad0f3002b04952ce4fb',
        ]];

        yield [[
            'algorithm' => HashAlgorithm::SHA256,
            'file' => UNIT_TEST_ROOT . '/Fixtures/lorem.txt',
            'digest' => '5f9bdb652714556b61c433c0cd08f0e237ade1efc315679ad2c0b84ba2eb4b08',
            'digest-x3' => 'aa62157cbc2f7bdbd158071c2b7d92911b3332518ed6ea120fc6778a0d07c972',
        ]];
    }
}
