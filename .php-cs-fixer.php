<?php

$finder = PhpCsFixer\Finder::create()
    ->in(["config", "src", "tests"]);

$config = new PhpCsFixer\Config();
return $config->setRules([
    '@Symfony' => true,
    'ordered_class_elements' => [
        'order' => [
            'case',
            'use_trait',
            'constant_public',
            'constant_protected',
            'constant_private',
            'property_public_static',
            'property_protected_static',
            'property_private_static',
            'method_public_static',
            'method_protected_static',
            'method_private_static',
            'property_public',
            'property_protected',
            'property_private',
            'construct',
            'destruct',
            'magic',
            'method_public',
            'method_protected',
            'method_private'
        ]
    ]
])
    ->setFinder($finder);