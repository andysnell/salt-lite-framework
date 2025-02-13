<?php

declare(strict_types=1);

use Rector\CodeQuality\Rector\ClassMethod\LocallyCalledStaticMethodToNonStaticRector;
use Rector\CodeQuality\Rector\Expression\InlineIfToExplicitIfRector;
use Rector\CodeQuality\Rector\Identical\FlipTypeControlToUseExclusiveTypeRector;
use Rector\CodeQuality\Rector\If_\ExplicitBoolCompareRector;
use Rector\Config\RectorConfig;
use Rector\DeadCode\Rector\ClassConst\RemoveUnusedPrivateClassConstantRector;
use Rector\DeadCode\Rector\ClassMethod\RemoveUnusedPrivateMethodRector;
use Rector\DeadCode\Rector\ClassMethod\RemoveUnusedPromotedPropertyRector;
use Rector\DeadCode\Rector\Property\RemoveUnusedPrivatePropertyRector;
use Rector\Php74\Rector\Closure\ClosureToArrowFunctionRector;
use Rector\PHPUnit\CodeQuality\Rector\Class_\PreferPHPUnitThisCallRector;

return RectorConfig::configure()
    ->withImportNames(importShortClasses: false)
    ->withCache(__DIR__ . '/build/rector')
    ->withRootFiles()
    ->withPaths([
        __DIR__ . '/bin',
        __DIR__ . '/config',
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ])
    ->withPhpSets(php84: true)
    ->withAttributesSets(all: true)
    ->withPreparedSets(
        deadCode: true,
        codeQuality: true,
        codingStyle: false,
        typeDeclarations: true,
        privatization: true,
        naming: false,
        instanceOf: true,
        earlyReturn: true,
        strictBooleans: false,
        carbon: false,
        rectorPreset: true,
        phpunitCodeQuality: true,
        doctrineCodeQuality: true,
    )->withSkip([
        ClosureToArrowFunctionRector::class,
        FlipTypeControlToUseExclusiveTypeRector::class,
        PreferPHPUnitThisCallRector::class,
        InlineIfToExplicitIfRector::class,
        LocallyCalledStaticMethodToNonStaticRector::class,
        ExplicitBoolCompareRector::class,

        // intentionally dead code used for testing
        RemoveUnusedPrivatePropertyRector::class => [
            __DIR__ . '/tests/Util/Helper/Fixture/Mirror.php',
            __DIR__ . '/tests/Util/Helper/Fixture/NestingObject.php',
            __DIR__ . '/tests/Util/Helper/Fixture/PropertyFixture.php',
        ],
        RemoveUnusedPromotedPropertyRector::class => [
            __DIR__ . '/tests/Util/Helper/Fixture/NestedObject.php',
            __DIR__ . '/tests/Util/Helper/Fixture/NestingObject.php',
        ],
        RemoveUnusedPrivateClassConstantRector::class => [
            __DIR__ . '/tests/Util/Helper/Fixture/Mirror.php',
        ],
        RemoveUnusedPrivateMethodRector::class => [
            __DIR__ . '/tests/Util/Helper/Fixture/Mirror.php',
        ],
    ]);
