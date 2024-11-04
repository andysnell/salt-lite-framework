<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Tests\Domain\Hash;

use PhoneBurner\SaltLite\Framework\Domain\Hash\Exceptions\InvalidHash;
use PhoneBurner\SaltLite\Framework\Domain\Hash\Hash;
use PhoneBurner\SaltLite\Framework\Domain\Hash\HashAlgorithm;
use PhoneBurner\SaltLite\Framework\Util\Filesystem\FileReader;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use const PhoneBurner\SaltLite\Framework\UNIT_TEST_ROOT;

class HashTest extends TestCase
{
    #[DataProvider('providesStringsWithValidHashFormats')]
    #[Test]
    public function make_returns_instance_with_hashed_string_and_algorithm(
        HashAlgorithm $algorithm,
        string $digest,
    ): void {
        $hash = Hash::make($digest, $algorithm);

        self::assertSame(\strtolower($digest), $hash->digest);
        self::assertSame(\strtolower($digest), $hash->digest());
        self::assertSame(\strtolower($digest), (string)$hash);
        self::assertSame($algorithm, $hash->algorithm);
        self::assertSame($algorithm, $hash->algorithm());
    }

    #[DataProvider('providesStringsWithInvalidHashFormats')]
    #[Test]
    public function make_checks_if_hashed_string_is_valid_format_for_algorithm(
        HashAlgorithm $algorithm,
        string $invalid_hash,
    ): void {
        $this->expectException(InvalidHash::class);
        Hash::make($invalid_hash, $algorithm);
    }

    #[DataProvider('providesStringTestCases')]
    #[Test]
    public function string_hashes_an_arbitrary_string_with_algorithm(array $test_case): void
    {
        $hash = Hash::string($test_case['content'], $test_case['algorithm']);

        self::assertSame($test_case['digest'], $hash->digest);
        self::assertSame($test_case['digest'], $hash->digest());
        self::assertSame($test_case['digest'], (string)$hash);
        self::assertSame($test_case['algorithm'], $hash->algorithm);
        self::assertSame($test_case['algorithm'], $hash->algorithm());
    }

    #[DataProvider('providesFileTestCases')]
    #[Test]
    public function file_hashes_an_arbitrary_file_with_algorithm(array $test_case): void
    {
        $hash = Hash::file($test_case['file'], $test_case['algorithm']);

        self::assertSame($test_case['digest'], $hash->digest);
        self::assertSame($test_case['digest'], $hash->digest());
        self::assertSame($test_case['digest'], (string)$hash);
        self::assertSame($test_case['algorithm'], $hash->algorithm);
        self::assertSame($test_case['algorithm'], $hash->algorithm());
    }

    #[DataProvider('providesFileTestCases')]
    #[Test]
    public function iterable_hashes_an_arbitrary_pump_iterator_with_algorithm(array $test_case): void
    {
        $hash = Hash::iterable(
            FileReader::make($test_case['file']),
            $test_case['algorithm'],
        );

        self::assertSame($test_case['digest'], $hash->digest);
        self::assertSame($test_case['digest'], $hash->digest());
        self::assertSame($test_case['digest'], (string)$hash);
        self::assertSame($test_case['algorithm'], $hash->algorithm);
        self::assertSame($test_case['algorithm'], $hash->algorithm());
    }

    #[DataProvider('providesFileTestCases')]
    #[Test]
    public function iterable_hashes_an_arbitrary_pump_iterator_recursively_with_algorithm(array $test_case): void
    {
        $hash = Hash::iterable([
            FileReader::make($test_case['file']),
        ], $test_case['algorithm']);

        self::assertSame($test_case['digest'], $hash->digest);
        self::assertSame($test_case['digest'], $hash->digest());
        self::assertSame($test_case['digest'], (string)$hash);
        self::assertSame($test_case['algorithm'], $hash->algorithm);
        self::assertSame($test_case['algorithm'], $hash->algorithm());

        $hash = Hash::iterable([
            FileReader::make($test_case['file']),
            FileReader::make($test_case['file']),
            FileReader::make($test_case['file']),
        ], $test_case['algorithm']);

        self::assertSame($test_case['digest-x3'], $hash->digest);
        self::assertSame($test_case['digest-x3'], $hash->digest());
        self::assertSame($test_case['digest-x3'], (string)$hash);
        self::assertSame($test_case['algorithm'], $hash->algorithm);
        self::assertSame($test_case['algorithm'], $hash->algorithm());
    }

    #[Test]
    public function is_returns_true_if_two_hashes_are_the_same_string_and_algorithm(): void
    {
        $hash_0 = Hash::string('foo bar baz', HashAlgorithm::BLAKE2B);
        $hash_1 = Hash::string('foo bar baz', HashAlgorithm::BLAKE2B);
        $hash_2 = Hash::string('wrong string', HashAlgorithm::BLAKE2B);
        $hash_3 = Hash::string('foo bar baz', HashAlgorithm::SHA1);
        $hash_4 = Hash::string('foo bar baz', HashAlgorithm::SHA1);
        $hash_5 = Hash::string('wrong string', HashAlgorithm::SHA1);

        self::assertTrue($hash_0->is($hash_0));
        self::assertTrue($hash_0->is($hash_1));
        self::assertFalse($hash_0->is($hash_2));
        self::assertFalse($hash_0->is($hash_3));
        self::assertTrue($hash_3->is($hash_4));
        self::assertFalse($hash_3->is($hash_5));
    }

    public static function providesStringTestCases(): \Generator
    {
        $test_case = [
            'content' => 'Lorem ipsum dolor sit amet...',
            'key' => '155e089312801dafa0c7da3856eab67e24f92274825274037bf8cf8e2a93c041',
            'wrong_content' => 'lorem ipsum dolor sit amet...',
            'wrong_key' => '3311e44682b167eeca7ea10caba53b56154cbd31f63e64c785416eb9d391b38d',
        ];

        yield [[
            ...$test_case,
            'algorithm' => HashAlgorithm::BLAKE2B,
            'digest' => 'cddc2151871e7aef21a728b0780c0d0a924fc7510c77538a3015a0ff71a9ddee',
        ]];

        yield [[
            ...$test_case,
            'algorithm' => HashAlgorithm::SHA1,
            'digest' => 'f9535ebf196065d80f94a57e865115d56bee595a',
        ]];

        yield [[
            ...$test_case,
            'algorithm' => HashAlgorithm::MD5,
            'digest' => 'ef1758dc3fd0f4ae305c449ba6ad0bd1',
        ]];
    }

    public static function providesFileTestCases(): \Generator
    {
        yield [[
            'algorithm' => HashAlgorithm::BLAKE2B,
            'file' => UNIT_TEST_ROOT . '/Fixtures/lorem.txt',
            'digest' => '6d5ec669b7d00274c1353bbb23d09b9daa95fde386a090832d2c0ca92655d53f',
            'digest-x3' => '465b53df509cc62e44f00e74e4c322d8030946c9344e5fda342303748ba56e2c',
        ]];

        yield [[
            'algorithm' => HashAlgorithm::XXH3,
            'file' => UNIT_TEST_ROOT . '/Fixtures/lorem.txt',
            'digest' => 'b4e78318e395880a',
            'digest-x3' => '4a70370c0528c4ca',
        ]];

        yield [[
            'algorithm' => HashAlgorithm::SHA512,
            'file' => UNIT_TEST_ROOT . '/Fixtures/lorem.txt',
            'digest' => 'd991bca1fd14bfefe6d6ab8dbd17ef96be2149f4cbb4b2830bf752c96a5a3b2a5cc1b2bea1520aff246cc39c44e05bf573e749060a048b09e0f78921661ba8bb',
            'digest-x3' => '83265fbd9f4640246bb88510268bf334bfe4ca550078af562e447b85f2e106a417d00a2605bee8d634bf3b5093e26c14fa73dbb05d6b62bfd8cdcdf670c4326c',
        ]];
    }

    public static function providesStringsWithValidHashFormats(): \Generator
    {
        yield [HashAlgorithm::BLAKE2B, '36d457859da599dd5d91c62f3879bb9a29374e75441b7d33343a19d5db39306d'];
        yield [HashAlgorithm::BLAKE2B, 'c168b0203132b736b5597b3a21cf52eabed4d99462d55ef8de38875ecd57bee4'];
        yield [HashAlgorithm::BLAKE2B, 'e02fcdb4783c1d4fdb5a6e4422d24f88f21d38d5cd923c3b3839cbc888eff943'];
        yield [HashAlgorithm::BLAKE2B, 'e23b10aa781610fd5fa64f195da052007d358c6085900288b82739b6c9a5a5d9'];
        yield [HashAlgorithm::BLAKE2B, '98937a8700a31f85a37bb94370a88d519728805b5d8df2dd41275f27272b8d63'];
        yield [HashAlgorithm::BLAKE2B, 'd2cd29f9a5c39df831d43539305dc208fdd192d5e16d1bb9416ed45f6401e592'];
        yield [HashAlgorithm::BLAKE2B, '36D457859da599dd5d91c62F3879bB9a29374e75441b7d33343a19d5DB39306d'];
        yield [HashAlgorithm::MD5, '9a84680cc71cda40f4efa870dd6c589f'];
        yield [HashAlgorithm::MD5, 'b882c820ebdb33b955e4eb315a015c38'];
        yield [HashAlgorithm::MD5, '246731a7cf61e5c8d76fe783bc21b50c'];
        yield [HashAlgorithm::MD5, 'a8740630daf0754e28724d49d88a431f'];
        yield [HashAlgorithm::MD5, '6666c244c4363795ba04a9ab972efa4e'];
        yield [HashAlgorithm::MD5, '7107a79b1627551bfc12b793bdc77acc'];
        yield [HashAlgorithm::MD5, '9A84680cc71CDA40f4efa870dd6C589f'];
        yield [HashAlgorithm::MD5, '9A84680CC71CDA40F4EFA870DD6C589F'];
        yield [HashAlgorithm::SHA1, "5b27f55ed926c0e1b7c55368c690785b6552fe0e"];
        yield [HashAlgorithm::SHA1, "69b8c54cfcc2510043d014242bc00257563849e1"];
        yield [HashAlgorithm::SHA1, "a31eb57cb73c0ac0f57d6f0e36916c6547b1e22e"];
        yield [HashAlgorithm::SHA1, "595fb2c6720f289935c3b4be42c069c0ba4a9eab"];
        yield [HashAlgorithm::SHA1, "1e6ac03cc70bbd37f777ce927b74a02c896af73c"];
        yield [HashAlgorithm::SHA1, "585027fd36b8341f773a6bc017a150aca16c97f1"];
        yield [HashAlgorithm::SHA1, '5B27f55ed926C0e1b7c55368C690785b6552fE0e'];
        yield [HashAlgorithm::SHA1, '5B27F55ED926C0E1B7C55368C690785B6552FE0E'];
    }

    public static function providesStringsWithInvalidHashFormats(): \Generator
    {
        yield 'blake2b_totally wrong' => [HashAlgorithm::BLAKE2B, 'Hello, World'];
        yield 'blake2b_empty_string' => [HashAlgorithm::BLAKE2B, ''];
        yield 'blake2b_right_length_wrong_chars' => [HashAlgorithm::BLAKE2B, 'mmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmm'];
        yield 'blake2b_one_invalid_char' => [HashAlgorithm::BLAKE2B, '36d457859da599dd5d91c62f38P9bb9a29374e75441b7d33343a19d5db39306d'];
        yield 'blake2b_one_char_too_many' => [HashAlgorithm::BLAKE2B, '36d457859da599dd5d91c62K3879bb9a29374e75441b7d33343a19d5db39306da'];
        yield 'blake2b_one_char_too_few' => [HashAlgorithm::BLAKE2B, '36d457859da599dd5d91c62K3879bb9a29374e75441b7d33343a19d5db39306'];
        yield 'blake2b_valid_hash_for_different_algo' => [HashAlgorithm::BLAKE2B, '8843d7f92416211de9ebb963ff4ce28125932878'];
        yield 'md5_totally wrong' => [HashAlgorithm::MD5, 'Hello, World'];
        yield 'md5_empty_string' => [HashAlgorithm::MD5, ''];
        yield 'md5_right_length_wrong_chars' => [HashAlgorithm::MD5, 'mmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmm'];
        yield 'md5_one_invalid_char' => [HashAlgorithm::MD5, '9a84680cc71cPa40f4efa870dd6c589f'];
        yield 'md5_one_char_too_many' => [HashAlgorithm::MD5, '9a84680cc71cda40f4efa870dd6c589fa'];
        yield 'md5_one_char_too_few' => [HashAlgorithm::MD5, '9a84680cc71cda40f4efa870dd6c589'];
        yield 'md5_valid_hash_for_different_algo' => [HashAlgorithm::MD5, '8843d7f92416211de9ebb963ff4ce28125932878'];
        yield 'sha1_totally wrong' => [HashAlgorithm::SHA1, 'Hello, World'];
        yield 'sha1_empty_string' => [HashAlgorithm::SHA1, ''];
        yield 'sha1_right_length_wrong_chars' => [HashAlgorithm::SHA1, 'mmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmm'];
        yield 'sha1_one_invalid_char' => [HashAlgorithm::SHA1, '5b27f55ed926c0r1b7c55368c690785b6552fe0e'];
        yield 'sha1_one_char_too_many' => [HashAlgorithm::SHA1, '5b27f55ed926c0e1b7c55368c690785b6552fe0e0'];
        yield 'sha1_one_char_too_few' => [HashAlgorithm::SHA1, '5b27f55ed926c0e1b7c55368c690785b6552fe0'];
        yield 'sha1_valid_hash_for_different_algo' => [HashAlgorithm::SHA1, 'e02fcdb4783c1d4fdb5a6e4422d24f88f21d38d5cd923c3b3839cbc888eff943'];
    }
}
