<?php

declare(strict_types=1);

use Rector\CodeQuality\Rector\ClassMethod\LocallyCalledStaticMethodToNonStaticRector;
use Rector\CodeQuality\Rector\If_\ExplicitBoolCompareRector;
use Rector\CodingStyle\Rector\FuncCall\CountArrayToEmptyArrayComparisonRector;
use Rector\Config\RectorConfig;
use Rector\Php80\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector;
use Rector\Php83\Rector\ClassMethod\AddOverrideAttributeToOverriddenMethodsRector;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;
use Rector\TypeDeclaration\Rector\Property\TypedPropertyFromAssignsRector;

try {
    return RectorConfig::configure()
        ->withParallel(
            maxNumberOfProcess: 1,
            jobSize: 20,
        )
        ->withPaths([
            __DIR__ . '/app',
            __DIR__ . '/config',
            __DIR__ . '/database',
            __DIR__ . '/routes',
            __DIR__ . '/tests',
        ])
        ->withSkip([
            __DIR__ . '/vendor',
            __DIR__ . '/storage',
            __DIR__ . '/bootstrap/cache',
            __DIR__ . '/node_modules',

            // Skip specific rules that conflict with Laravel conventions
            LocallyCalledStaticMethodToNonStaticRector::class,
            ExplicitBoolCompareRector::class,
        ])
        ->withPhpSets(
            php84: true,
        )
        ->withSets([
            LevelSetList::UP_TO_PHP_84,
            SetList::CODE_QUALITY,
            SetList::DEAD_CODE,
            SetList::EARLY_RETURN,
            SetList::TYPE_DECLARATION,
            SetList::PRIVATIZATION,
        ])
        ->withRules([
            // PHP 8.0+ - Constructor promotion (not in sets by default)
            ClassPropertyAssignToConstructorPromotionRector::class,

            // PHP 8.3+ - Override attributes (not in sets by default)
            AddOverrideAttributeToOverriddenMethodsRector::class,

            // Code Quality - Count to empty array (not in sets by default)
            CountArrayToEmptyArrayComparisonRector::class,

            // Type Declarations - Typed property from assigns (not in sets by default)
            TypedPropertyFromAssignsRector::class,
        ])
        ->withPreparedSets(
            deadCode: true,
            codeQuality: true,
            codingStyle: false, // Disabled to avoid conflicts with Laravel Pint
            typeDeclarations: true,
            privatization: true,
            earlyReturn: true,
            strictBooleans: false, // Laravel prefers implicit boolean checks
        )
        ->withImportNames(
            importNames: true,
            importDocBlockNames: true,
            importShortClasses: false,
            removeUnusedImports: true,
        );
} catch (\Rector\Exception\Configuration\InvalidConfigurationException $e) {
    echo 'Rector configuration error: ' . $e->getMessage() . PHP_EOL;
    exit(1);
}
