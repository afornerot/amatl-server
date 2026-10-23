<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Symfony\Set\SymfonySetList;
use Rector\ValueObject\PhpVersion;

return RectorConfig::configure()
    ->withPaths([
        __DIR__.'/src',
    ])
    ->withPhpVersion(PhpVersion::PHP_85)
    ->withSets([
        LevelSetList::UP_TO_PHP_85,
        SymfonySetList::COMPOSER_BASED,
        SymfonySetList::SYMFONY_CODE_QUALITY,
        SymfonySetList::ANNOTATIONS_TO_ATTRIBUTES,
    ])
    ->withSkip([
        // Non appliqués : modifient la signature publique des contrôleurs
        \Rector\Symfony\CodeQuality\Rector\ClassMethod\RemoveUnusedRequestParamRector::class,
        \Rector\Php84\Rector\MethodCall\NewMethodCallWithoutParenthesesRector::class,
        // Non appliqués : refonte du typage des repositories
        \Rector\Php55\Rector\String_\StringClassNameToClassConstantRector::class,
        \Rector\Php80\Rector\FuncCall\ClassOnObjectRector::class,
        // Non appliqué : constantes HTTP à la place des codes littéraux
        \Rector\Symfony\CodeQuality\Rector\BinaryOp\ResponseStatusCodeRector::class,
    ]);
