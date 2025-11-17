<?php

declare(strict_types=1);

// Docs: https://getrector.com
use Rector\Exception\Configuration\InvalidConfigurationException;
use Rector\Symfony\Bridge\Symfony\Routing\SymfonyRoutesProvider;
use Rector\Symfony\Contract\Bridge\Symfony\Routing\SymfonyRoutesProviderInterface;
use Rector\TypeDeclaration\Rector\StmtsAwareInterface\DeclareStrictTypesRector;

try {
    return Rector\Config\RectorConfig::configure()
        ->withPaths([
            __DIR__.'/bin/console',
            __DIR__.'/bin/phpunit',
            __DIR__.'/src',
            __DIR__.'/tests',
        ])
        ->withSymfonyContainerPhp(__DIR__.'/tests/symfony-container.php')
        ->registerService(SymfonyRoutesProvider::class, SymfonyRoutesProviderInterface::class)
        ->withPhpSets()
        ->withPreparedSets(
            deadCode: true,
            codeQuality: true,
            codingStyle: true,
            typeDeclarations: true,
            typeDeclarationDocblocks: true,
            privatization: true,
            naming: true,
            instanceOf: true,
            earlyReturn: true,
            phpunitCodeQuality: true,
            doctrineCodeQuality: true,
            symfonyCodeQuality: true,
            symfonyConfigs: true,
        )
        ->withComposerBased(
            twig: true,
            doctrine: true,
            phpunit: true,
            symfony: true
        )
        ->withRules([
            DeclareStrictTypesRector::class,
        ])
    ;
} catch (InvalidConfigurationException $exception) {
    dd($exception);
}
