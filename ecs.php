<?php

declare(strict_types=1);

use PhpCsFixer\Fixer\Import\NoUnusedImportsFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;
use Symplify\EasyCodingStandard\ValueObject\Set\SetList;

return ECSConfig::configure()
    ->withPaths([
        __DIR__.'/about',
        __DIR__.'/country',
        __DIR__.'/details',
        __DIR__.'/home',
        __DIR__.'/region',
    ])
    ->withRootFiles()
    ->withRules([
        NoUnusedImportsFixer::class,
    ])
    ->withSets([
        // run and fix, one by one
        SetList::SPACES,
        SetList::ARRAY,
        SetList::DOCBLOCK,
        SetList::NAMESPACES,
        SetList::COMMENTS,
        SetList::PSR_12,
        SetList::CLEAN_CODE,
        SetList::CONTROL_STRUCTURES,
        SetList::LARAVEL,
    ]);
