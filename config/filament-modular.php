<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Pages
    |--------------------------------------------------------------------------
    |
    | This is the namespace and directory that Filament will automatically
    | register pages from. You may also register pages here.
    | Just write the simple directory name that is exists in the module directory.
    |
    */

    'pages' => [
        'namespace' => '\\Pages',
        'path' => '/Pages',
    ],

    /*
    |--------------------------------------------------------------------------
    | Resources
    |--------------------------------------------------------------------------
    |
    | This is the namespace and directory that Filament will automatically
    | register resources from. You may also register resources here.
    | Just write the simple directory name that is exists in the module directory.
    |
    */

    'resources' => [
        'namespace' => '\\Resources',
        'path' => '/Resources',
    ],

    /*
    |--------------------------------------------------------------------------
    | Widgets
    |--------------------------------------------------------------------------
    |
    | This is the namespace and directory that Filament will automatically
    | register dashboard widgets from. You may also register widgets here.
    | Just write the simple directory name that is exists in the module directory.
    |
    */

    'widgets' => [
        'namespace' => '\\Widgets',
        'path' => '/Widgets',
    ],

    /*
    |--------------------------------------------------------------------------
    | Livewire
    |--------------------------------------------------------------------------
    |
    | This is the namespace and directory that Filament will automatically
    | register Livewire components inside.
    | Just write the simple directory name that is exists in the module directory.
    | Other directories like Resources, Pages and Widgets use this as the base
    | directory.
    | **** It will be the parent directory ****
    | for e.g.: imagine u have User module, so the paths will be as mentioned below:
    | Modules > User > Admin
    | Modules > User > Admin > Resources
    | Modules > User > Admin > Pages
    | Modules > User > Admin > Widget
    |
    */

    'livewire' => [
        'namespace' => '\\Admin',
        'path' => '/Admin',
    ],

    /*
    |--------------------------------------------------------------------------
    | Modules
    |--------------------------------------------------------------------------
    |
    | This is the namespace of your modules directory that Filament use
    | to register your resources and other things inside it.
    |
    */

    'modules' => [
        'namespace' => config('modules.namespace', 'Modules'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Views
    |--------------------------------------------------------------------------
    |
    | This is the views config for the modular support of the filament package.
    | Use it once you installed this package and never change it after creating
    | resources and other things.
    | Making changes in existing projects may lead to unexpected errors.
    |
    | Set 'in_module' to true if you want to get your views inside the module
    | directory, otherwise; set it 'false' and set the 'path' to where you want
    | to your views will be stored.
    |
    | In case of using custom paths, you MUST provide a namespace for views that
    | is used to find views in your specified path by laravel.
    |
    */

    'views' => [
        'in_module' => true,
        'path' => null,
        'namespace' => null,
    ],
];
