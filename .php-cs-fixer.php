<?php

declare(strict_types=1);

$finder = PhpCsFixer\Finder::create()
    ->in([
        __DIR__ . '/src',
        __DIR__ . '/tests',
        __DIR__ . '/public',
        __DIR__ . '/bin',
    ])
    ->name('*.php');

return (new PhpCsFixer\Config())
    ->setRiskyAllowed(true)
    ->setRules([
        '@PSR12'                             => true,
        'declare_strict_types'               => true,
        'array_syntax'                       => ['syntax' => 'short'],
        'ordered_imports'                    => ['sort_algorithm' => 'alpha'],
        'no_unused_imports'                  => true,
        'trailing_comma_in_multiline'        => ['elements' => ['arrays', 'parameters', 'arguments']],
        'single_quote'                       => true,
        'no_extra_blank_lines'               => ['tokens' => ['extra', 'throw', 'use']],
        'blank_line_before_statement'        => ['statements' => ['return', 'throw']],
        'binary_operator_spaces'             => ['default' => 'single_space'],
        'concat_space'                       => ['spacing' => 'one'],
        'phpdoc_align'                       => ['align' => 'left'],
        'phpdoc_scalar'                      => true,
        'phpdoc_trim'                        => true,
        'no_superfluous_phpdoc_tags'         => ['allow_mixed' => true],
        'return_type_declaration'            => ['space_before' => 'none'],
        'class_attributes_separation'        => ['elements' => ['method' => 'one']],
        'method_argument_space'              => ['on_multiline' => 'ensure_fully_multiline'],
        'nullable_type_declaration_for_default_null_value' => true,
        'modernize_types_casting'            => true,
        'void_return'                        => true,
    ])
    ->setFinder($finder);
