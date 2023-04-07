<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Pages
    |--------------------------------------------------------------------------
    |
    | This is the namespace and directory that Filament will automatically
    | register pages from. You may also register pages here.
    |
    */

    'pages' => [
        'namespace' => '%s\\%s\\Pages',
        'path'      => '%s/Pages',
        'simple'    => [
            'namespace' => '\\Pages',
            'path'      => '/Pages',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Resources
    |--------------------------------------------------------------------------
    |
    | This is the namespace and directory that Filament will automatically
    | register resources from. You may also register resources here.
    |
    */

    'resources' => [
        'namespace' => '%s\\%s\\Resources',
        'path'      => '%s/Resources',
        'simple'    => [
            'namespace' => '\\Resources',
            'path'      => '/Resources',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Widgets
    |--------------------------------------------------------------------------
    |
    | This is the namespace and directory that Filament will automatically
    | register dashboard widgets from. You may also register widgets here.
    |
    */

    'widgets' => [
        'namespace' => '%s\\%s\\Widgets',
        'path'      => '%s/Widgets',
        'simple'    => [
            'namespace' => '\\Widgets',
            'path'      => '/Widgets',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Livewire
    |--------------------------------------------------------------------------
    |
    | This is the namespace and directory that Filament will automatically
    | register Livewire components inside.
    |
    */

    'livewire' => [
        'namespace' => '%s\\%s\\Admin',
        'path'      => '%s/Admin',
        'simple'    => [
            'namespace' => '\\Admin',
            'path'      => '/Admin',
        ],
    ],
];
