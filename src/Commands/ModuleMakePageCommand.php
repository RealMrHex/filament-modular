<?php

namespace RealMrHex\FilamentModular\Commands;

use Filament\Support\Commands\Concerns\CanManipulateFiles;
use Filament\Support\Commands\Concerns\CanValidateInput;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Nwidart\Modules\Traits\ModuleCommandTrait;
use RealMrHex\FilamentModular\Commands\Concerns\CommandSetup;
use RealMrHex\FilamentModular\Commands\Concerns\InteractsWithFileNames;

class ModuleMakePageCommand extends Command
{
    use CommandSetup;
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

        $this->init();

        $path = $this->pages_path;
        $resourcePath = $this->resources_path;
        $namespace = $this->pages_namespace;
        $resourceNamespace = $this->resources_namespace;
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
                ->prepend($this->module_path.'/Resources/views/')
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
