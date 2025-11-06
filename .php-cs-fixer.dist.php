<?php

declare(strict_types=1);

$finder = new PhpCsFixer\Finder()
    ->in([
        __DIR__.'/bin',
        __DIR__.'/public',
        __DIR__.'/src',
        __DIR__.'/tests',
    ])
    ->append([
        __DIR__.'/.php-cs-fixer.dist.php',
        __DIR__.'/importmap.php',
        __DIR__.'/rector.php']
    )
;

return new PhpCsFixer\Config()
    ->setRules([
        '@Symfony'                    => true,
        'yoda_style'                  => false,
        'binary_operator_spaces'      => [
            'operators' => [
                '=>' => 'align_single_space_by_scope',
                '='  => 'align_single_space',
            ],
        ],
        'class_attributes_separation' => [
            'elements' => [
                'method'       => 'one',
                'trait_import' => 'one',
            ],
        ],
        'increment_style'             => [
            'style' => 'post',
        ],
    ])
    ->setFinder($finder)
;
