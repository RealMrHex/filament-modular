<?php

namespace RealMrHex\FilamentModular\Commands;

use Filament\Support\Commands\Concerns\CanManipulateFiles;
use Filament\Support\Commands\Concerns\CanValidateInput;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Nwidart\Modules\Laravel\Module;
use Nwidart\Modules\Traits\ModuleCommandTrait;
use RealMrHex\FilamentModular\Commands\Concerns\InteractsWithFileNames;

class ModuleMakePageCommand extends Command
{
    use CanManipulateFiles;
    use CanValidateInput;
    use InteractsWithFileNames;
    use ModuleCommandTrait;

    protected $description = 'Creates a Filament page class and view.';

    protected $signature = 'module:make-filament-page {name?} {module?} {--R|resource=} {--T|type=} {--F|force}';

    public function handle(): int
    {
        $page = (string) Str::of($this->argument('name') ?? $this->askRequired('Name (e.g. `Settings`)', 'name'))
                           ->trim('/')
                           ->trim('\\')
                           ->trim(' ')
                           ->replace('/', '\\');

        $module_name = $this->ensureArg('module', 'Module Name (e.g. `User`)');
        $module_name = Str::of($module_name)->studly()->toString();
        $module = null;

        $base = config('filament-modular.livewire.path');

        try {
            /**
             * @var Module $module
             */
            $module = app('modules')->findOrFail($module_name);
        } catch (\Throwable $exception) {
            $this->error('module not found');

            return static::INVALID;
        }

        $_directory_format = '%s/'.Str::replaceFirst('/', '', config('filament-modular.livewire.path'));
        $_namespace_format = '%s\\%s\\'.Str::replaceFirst('\\', '', config('filament-modular.livewire.namespace'));
        $_module_namespace = config('filament-modular.modules.namespace');

        $module_path = $module->getPath();
        $module_name = $module->getName();

        $module_directory = sprintf($_directory_format, $module_path);
        $module_namespace = sprintf($_namespace_format, $_module_namespace, $module_name);

        $_widgets_format = '%s/'.Str::replaceFirst('/', '', config('filament-modular.widgets.path'));
        $_resources_format = '%s/'.Str::replaceFirst('/', '', config('filament-modular.resources.path'));
        $_pages_format = '%s/'.Str::replaceFirst('/', '', config('filament-modular.pages.path'));

        $widgets_path = sprintf($_widgets_format, $module_directory);
        $resources_path = sprintf($_resources_format, $module_directory);
        $pages_path = sprintf($_pages_format, $module_directory);

        $_widgets_namespace_format = '%s\\'.Str::replaceFirst('\\', '', config('filament-modular.widgets.namespace'));
        $_resources_namespace_format = '%s\\'.Str::replaceFirst('\\', '', config('filament-modular.resources.namespace'));
        $_pages_namespace_format = '%s\\'.Str::replaceFirst('\\', '', config('filament-modular.pages.namespace'));

        $widgets_namespace = sprintf($_widgets_namespace_format, $module_namespace);
        $resources_namespace = sprintf($_resources_namespace_format, $module_namespace);
        $pages_namespace = sprintf($_pages_namespace_format, $module_namespace);

        $path = $pages_path;
        $resourcePath = $resources_path;
        $namespace = $pages_namespace;
        $resourceNamespace = $resources_namespace;
        $views_namespace = config('filament-modular.views.namespace');

        $pageClass = (string) Str::of($page)->afterLast('\\');
        $pageNamespace = Str::of($page)->contains('\\')
            ?
            (string) Str::of($page)->beforeLast('\\')
            :
            '';

        $resource = null;
        $resourceClass = null;
        $resourcePage = null;

        $resourceInput = $this->option('resource') ?? $this->ask('(Optional) Resource (e.g. `UserResource`)');

        if (null !== $resourceInput) {
            $resource = (string) Str::of($resourceInput)
                                   ->studly()
                                   ->trim('/')
                                   ->trim('\\')
                                   ->trim(' ')
                                   ->replace('/', '\\');

            if (!Str::of($resource)->endsWith('Resource')) {
                $resource .= 'Resource';
            }

            $resourceClass = (string) Str::of($resource)
                                        ->afterLast('\\');

            $resourcePage = $this->option('type') ?? $this->choice(
                'Which type of page would you like to create?',
                [
                    'custom' => 'Custom',
                    'ListRecords' => 'List',
                    'CreateRecord' => 'Create',
                    'EditRecord' => 'Edit',
                    'ViewRecord' => 'View',
                    'ManageRecords' => 'Manage',
                ],
                'custom',
            );
        }

        $view = Str::of($page)
                   ->prepend(
                       (string) Str::of(null === $resource ? "{$namespace}\\" : "{$resourceNamespace}\\{$resource}\\pages\\")
                                  ->replace('App\\', ''),
                   )
                   ->replace('\\', '/')
                   ->replace('Modules/', '')
                   ->explode('/')
                   ->map(fn ($segment) => Str::lower(Str::kebab($segment)))
                   ->implode('.');

        $path = (string) Str::of($page)
                           ->prepend('/')
                           ->prepend(null === $resource ? $path : "{$resourcePath}\\{$resource}\\Pages\\")
                           ->replace('\\', '/')
                           ->replace('//', '/')
                           ->append('.php');

        $theme_dir = config('filament-modular.views.path');
        $in_module = config('filament-modular.views.in_module');

        if ($in_module) {
            $kebab = explode('.', $view)[0];

            $viewPath = Str::of($view)
                           ->replace('.', '/')
                           ->replace("$kebab/", '')
                           ->prepend($module_path.'/Resources/views/')
                           ->append('.blade.php')
                           ->replace('modules/', '')
                           ->toString();

            $view = Str::of($view)->replace("$kebab.", '')->toString();
            $namespaced_view = "$kebab::$view";
        } else {
            $viewPath = Str::of($view)
                           ->replace('.', '/')
                           ->prepend($theme_dir)
                           ->append('.blade.php')
                           ->replace('modules/', '')
                           ->toString();

            $namespaced_view = "$views_namespace::$view";
        }

        $files = array_merge(
            [$path],
            'custom' === $resourcePage ? [$viewPath] : [],
        );

        if (!$this->option('force') && $this->checkForCollision($files)) {
            return static::INVALID;
        }

        if (null === $resource) {
            $this->copyStubToApp('Page', $path, [
                'class' => $pageClass,
                'namespace' => $namespace.('' !== $pageNamespace ? "\\{$pageNamespace}" : ''),
                'view' => $namespaced_view,
            ]);
        } else {
            $this->copyStubToApp('custom' === $resourcePage ? 'CustomResourcePage' : 'ResourcePage', $path, [
                'baseResourcePage' => 'Filament\\Resources\\Pages\\'.('custom' === $resourcePage ? 'Page' : $resourcePage),
                'baseResourcePageClass' => 'custom' === $resourcePage ? 'Page' : $resourcePage,
                'namespace' => "{$resourceNamespace}\\{$resource}\\Pages".('' !== $pageNamespace ? "\\{$pageNamespace}" : ''),
                'resource' => "{$resourceNamespace}\\{$resource}",
                'resourceClass' => $resourceClass,
                'resourcePageClass' => $pageClass,
                'view' => $namespaced_view,
            ]);
        }

        if (null === $resource || 'custom' === $resourcePage) {
            $this->copyStubToApp('PageView', $viewPath);
        }

        $this->info("Successfully created {$page}!");

        if (null !== $resource) {
            $this->info("Make sure to register the page in `{$resourceClass}::getPages()`.");
        }

        return static::SUCCESS;
    }
}
