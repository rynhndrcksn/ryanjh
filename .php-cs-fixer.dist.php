<?php

declare(strict_types=1);

$finder = new PhpCsFixer\Finder()
    ->in([
        __DIR__.'/src',
        __DIR__.'/tests',
    ])
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
