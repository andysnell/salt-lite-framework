<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Database\Doctrine;

use Doctrine\DBAL\Types\Types as DoctrineTypes;

final class Types
{
    // Built In Doctrine Types
    public const string ASCII_STRING = DoctrineTypes::ASCII_STRING;
    public const string BIGINT = DoctrineTypes::BIGINT;
    public const string BINARY = DoctrineTypes::BINARY;
    public const string BLOB = DoctrineTypes::BLOB;
    public const string BOOLEAN = DoctrineTypes::BOOLEAN;
    public const string DATE_MUTABLE = DoctrineTypes::DATE_MUTABLE;
    public const string DATE_IMMUTABLE = DoctrineTypes::DATE_IMMUTABLE;
    public const string DATEINTERVAL = DoctrineTypes::DATEINTERVAL;
    public const string DATETIME_MUTABLE = DoctrineTypes::DATETIME_MUTABLE;
    public const string DATETIME_IMMUTABLE = DoctrineTypes::DATETIME_IMMUTABLE;
    public const string DATETIMETZ_MUTABLE = DoctrineTypes::DATETIMETZ_MUTABLE;
    public const string DATETIMETZ_IMMUTABLE = DoctrineTypes::DATETIMETZ_IMMUTABLE;
    public const string DECIMAL = DoctrineTypes::DECIMAL;
    public const string FLOAT = DoctrineTypes::FLOAT;
    public const string GUID = DoctrineTypes::GUID;
    public const string INTEGER = DoctrineTypes::INTEGER;
    public const string JSON = DoctrineTypes::JSON;
    public const string SIMPLE_ARRAY = DoctrineTypes::SIMPLE_ARRAY;
    public const string SMALLINT = DoctrineTypes::SMALLINT;
    public const string STRING = DoctrineTypes::STRING;
    public const string TEXT = DoctrineTypes::TEXT;
    public const string TIME_MUTABLE = DoctrineTypes::TIME_MUTABLE;
    public const string TIME_IMMUTABLE = DoctrineTypes::TIME_IMMUTABLE;

    private static bool $called = false;

    private function __construct()
    {
    }

    public static function register(): void
    {
        if (self::$called) {
            return;
        }

//        foreach (self::REGISTRATION_MAP as $name => $class_name) {
//            if (Type::hasType($name)) {
//                continue;
//            }
//            Type::addType($name, $class_name);
//        }
//
//        foreach (EnumObjectTypes::REGISTRATION_MAP as $enum_class) {
//            if (Type::hasType($enum_class)) {
//                continue;
//            }
//            Type::getTypeRegistry()->register($enum_class, EnumObjectType::make($enum_class));
//        }

        self::$called = true;
    }
}
