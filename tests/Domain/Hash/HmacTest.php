<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Tests\Domain\Hash;

use PhoneBurner\SaltLite\Framework\Domain\Hash\Exceptions\InvalidHash;
use PhoneBurner\SaltLite\Framework\Domain\Hash\HashAlgorithm;
use PhoneBurner\SaltLite\Framework\Domain\Hash\Hmac;
use PhoneBurner\SaltLite\Framework\Domain\Hash\HmacKey;
use PhoneBurner\SaltLite\Framework\Util\Filesystem\FileReader;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use const PhoneBurner\SaltLite\Framework\UNIT_TEST_ROOT;

class HmacTest extends TestCase
{
    private const string HMAC_KEY = '8df08d18de44263419a39074956dc2ef8a3a0b1b26db984282bfcfc202cda41d';

    #[DataProvider('providesStringsWithValidHashFormats')]
    #[Test]
    public function make_returns_hmac_from_hashed_string_and_algorithm(
        HashAlgorithm $algorithm,
        string $digest,
    ): void {
        $hmac = Hmac::make($digest, $algorithm);

        self::assertSame(\strtolower($digest), $hmac->digest);
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
        $key = HmacKey::make(self::HMAC_KEY);
        $hmac = Hmac::string('foo bar baz', $key, $algorithm);

        self::assertSame($digest, $hmac->digest);
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
            HmacKey::make(self::HMAC_KEY),
            $test_case['algorithm'],
        );

        self::assertSame($test_case['digest'], $hmac->digest);
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
            HmacKey::make(self::HMAC_KEY),
            $test_case['algorithm'],
        );

        self::assertSame($test_case['digest'], $hmac->digest);
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
        ], HmacKey::make(self::HMAC_KEY), $test_case['algorithm']);

        self::assertSame($test_case['digest'], $hmac->digest);
        self::assertSame($test_case['digest'], $hmac->digest());
        self::assertSame($test_case['digest'], (string)$hmac);
        self::assertSame($test_case['algorithm'], $hmac->algorithm);
        self::assertSame($test_case['algorithm'], $hmac->algorithm());

        $hmac = Hmac::iterable([
            FileReader::make($test_case['file']),
            FileReader::make($test_case['file']),
            FileReader::make($test_case['file']),
        ], HmacKey::make(self::HMAC_KEY), $test_case['algorithm']);

        self::assertSame($test_case['digest-x3'], $hmac->digest);
        self::assertSame($test_case['digest-x3'], $hmac->digest());
        self::assertSame($test_case['digest-x3'], (string)$hmac);
        self::assertSame($test_case['algorithm'], $hmac->algorithm);
        self::assertSame($test_case['algorithm'], $hmac->algorithm());
    }

    #[Test]
    public function is_returns_true_if_two_hmacs_are_the_same_string_and_algorithm(): void
    {
        $key_0 = HmacKey::generate();
        $key_1 = HmacKey::generate();

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
        foreach ([HashAlgorithm::BLAKE2B, HashAlgorithm::SHA256, HashAlgorithm::SHA512_256] as $algo) {
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
        foreach ([HashAlgorithm::BLAKE2B, HashAlgorithm::SHA256, HashAlgorithm::SHA512_256] as $algo) {
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
        yield [HashAlgorithm::BLAKE2B, '7e41fb74e95bd0d9ec92ca620b215634ed28c0359f03e0b1afb9653069aca659'];
        yield [HashAlgorithm::SHA3_256, 'b0cc903e780822b2c847600b3376ec1c54d55349d2ac054d1ba5fb9e7a84b0c0'];
        yield [HashAlgorithm::SHA256, '9479aabd3989b8107701ea344bf64577ed2dd1dbeccd223ced018be650ff3c7d'];
    }

    public static function providesFileTestCases(): \Generator
    {
        yield [[
            'algorithm' => HashAlgorithm::BLAKE2B,
            'file' => UNIT_TEST_ROOT . '/Fixtures/lorem.txt',
            'digest' => '1446cc460268abbc04c0b9d66ada1859b2848d79c63e82efd201f43540dba3c5',
            'digest-x3' => '7c7c042d0b7cc00606eb6ca95eac9b86da2d5b211fbcbdd1b2f49e98edc231ba',
        ]];

        yield [[
            'algorithm' => HashAlgorithm::SHA3_256,
            'file' => UNIT_TEST_ROOT . '/Fixtures/lorem.txt',
            'digest' => '40e17dfdb00c3c5836d2e608b44fac50a82d686bd6a374f6dff515060f36a4ab',
            'digest-x3' => '9729c6f9caa0a0bc7319c62fa5dd311091c3b057ea6d1abb7f7a28d8a23e1b90',
        ]];

        yield [[
            'algorithm' => HashAlgorithm::SHA256,
            'file' => UNIT_TEST_ROOT . '/Fixtures/lorem.txt',
            'digest' => '9018bd9e5d71332f8947fea806f52f7678aa4a0be9b360b14baa08dc8ccbed12',
            'digest-x3' => '4fa61469f0c1dd1b58010be3f0bc80e57a025dd359bf6e43f348cb6f88324b96',
        ]];
    }
}
