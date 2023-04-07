<?php

namespace RealMrHex\FilamentModular\Commands;

use Filament\Support\Commands\Concerns\CanManipulateFiles;
use Filament\Support\Commands\Concerns\CanValidateInput;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Nwidart\Modules\Laravel\Module;
use Nwidart\Modules\Traits\ModuleCommandTrait;
use RealMrHex\FilamentModular\Commands\Concerns\InteractsWithFileNames;
use Throwable;

class ModuleMakeWidgetCommand extends Command
{
    use CanManipulateFiles;
    use CanValidateInput;
    use InteractsWithFileNames;
    use ModuleCommandTrait;

    protected $description = 'Creates a Filament widget class.';

    protected $signature = 'module:make-filament-widget {name?} {module?} {--R|resource=} {--C|chart} {--T|table} {--S|stats-overview} {--F|force}';

    public function handle(): int
    {
        $module_name = $this->ensureArg('module', 'Module Name (e.g. `User`)');
        $module_name = Str::of($module_name)->studly()->toString();
        $module = null;

        $base = config('filament-modular.livewire.path');

        try
        {
            /**
             * @var Module $module
             */
            $module = app('modules')->findOrFail($module_name);
        }
        catch (Throwable $exception)
        {
            $this->error('module not found');
            return static::INVALID;
        }

        $_directory_format = '%s/' . Str::replaceFirst('/', '', config('filament-modular.livewire.path'));
        $_namespace_format = '%s\\%s\\' . Str::replaceFirst('\\', '', config('filament-modular.livewire.namespace'));
        $_module_namespace = config('filament-modular.modules.namespace');

        $module_path = $module->getPath();
        $module_name = $module->getName();

        $module_directory = sprintf($_directory_format, $module_path);
        $module_namespace = sprintf($_namespace_format, $_module_namespace, $module_name);

        $_widgets_format = '%s/' . Str::replaceFirst('/', '', config('filament-modular.widgets.path'));
        $_resources_format = '%s/' . Str::replaceFirst('/', '', config('filament-modular.resources.path'));
        $_pages_format = '%s/' . Str::replaceFirst('/', '', config('filament-modular.pages.path'));

        $widgets_path = sprintf($_widgets_format, $module_directory);
        $resources_path = sprintf($_resources_format, $module_directory);
        $pages_path = sprintf($_pages_format, $module_directory);

        $_widgets_namespace_format = '%s\\' . Str::replaceFirst('\\', '', config('filament-modular.widgets.namespace'));
        $_resources_namespace_format = '%s\\' . Str::replaceFirst('\\', '', config('filament-modular.resources.namespace'));
        $_pages_namespace_format = '%s\\' . Str::replaceFirst('\\', '', config('filament-modular.pages.namespace'));

        $widgets_namespace = sprintf($_widgets_namespace_format, $module_namespace);
        $resources_namespace = sprintf($_resources_namespace_format, $module_namespace);
        $pages_namespace = sprintf($_pages_namespace_format, $module_namespace);

        $views_namespace = config('filament-modular.views.namespace');

        $theme_dir = config('filament-modular.views.path');
        $in_module = config('filament-modular.views.in_module');

        $path = $widgets_path;
        $resourcePath = $resources_path;
        $namespace = $widgets_namespace;
        $resourceNamespace = $resources_namespace;

        $widget = (string) Str::of($this->argument('name') ?? $this->askRequired('Name (e.g. `BlogPostsChart`)', 'name'))
            ->trim('/')
            ->trim('\\')
            ->trim(' ')
            ->replace('/', '\\');

        $widgetClass = (string) Str::of($widget)->afterLast('\\');
        $widgetNamespace = Str::of($widget)->contains('\\') ?
            (string) Str::of($widget)->beforeLast('\\') :
            '';

        $resource = null;
        $resourceClass = null;

        $resourceInput = $this->option('resource') ?? $this->ask('(Optional) Resource (e.g. `BlogPostResource`)');

        if ($resourceInput !== null) {
            $resource = (string) Str::of($resourceInput)
                ->studly()
                ->trim('/')
                ->trim('\\')
                ->trim(' ')
                ->replace('/', '\\');

            if (! Str::of($resource)->endsWith('Resource')) {
                $resource .= 'Resource';
            }

            $resourceClass = (string) Str::of($resource)
                ->afterLast('\\');
        }

        $view = Str::of($widget)->prepend(
            (string) Str::of($resource === null ? "{$namespace}\\" : "{$resourceNamespace}\\{$resource}\\widgets\\")
                ->replace('App\\', '')
        )
            ->replace('\\', '/')
            ->replace('Modules/', '')
            ->explode('/')
            ->map(fn ($segment) => Str::lower(Str::kebab($segment)))
            ->implode('.');

        $path = (string) Str::of($widget)
            ->prepend('/')
            ->prepend($resource === null ? $path : "{$resourcePath}\\{$resource}\\Widgets\\")
            ->replace('\\', '/')
            ->replace('//', '/')
            ->append('.php');

        if ($in_module)
        {
            $kebab = explode('.', $view)[0];

            $viewPath = Str::of($view)
                           ->replace('.', '/')
                           ->replace("$kebab/", '')
                           ->prepend($module_path . '/Resources/views/')
                           ->append('.blade.php')
                           ->replace('modules/', '')
                           ->toString();

            $view = Str::of($view)->replace("$kebab.", '')->toString();
            $namespaced_view = "$kebab::$view";
        }
        else
        {
            $viewPath = Str::of($view)
                           ->replace('.', '/')
                           ->prepend($theme_dir)
                           ->append('.blade.php')
                           ->replace('modules/', '')
                           ->toString();
            $namespaced_view = "$views_namespace::$view";
        }

        if (! $this->option('force') && $this->checkForCollision([
            $path,
            ($this->option('stats-overview') || $this->option('chart')) ?: $viewPath,
        ])) {
            return static::INVALID;
        }

        if ($this->option('chart')) {
            $chart = $this->choice(
                'Chart type',
                [
                    'Bar chart',
                    'Bubble chart',
                    'Doughnut chart',
                    'Line chart',
                    'Pie chart',
                    'Polar area chart',
                    'Radar chart',
                    'Scatter chart',
                ],
            );

            $this->copyStubToApp('ChartWidget', $path, [
                'class' => $widgetClass,
                'namespace' => filled($resource) ? "{$resourceNamespace}\\{$resource}\\Widgets" . ($widgetNamespace !== '' ? "\\{$widgetNamespace}" : '') : $namespace . ($widgetNamespace !== '' ? "\\{$widgetNamespace}" : ''),
                'chart' => Str::studly($chart),
            ]);
        } elseif ($this->option('table')) {
            $this->copyStubToApp('TableWidget', $path, [
                'class' => $widgetClass,
                'namespace' => filled($resource) ? "{$resourceNamespace}\\{$resource}\\Widgets" . ($widgetNamespace !== '' ? "\\{$widgetNamespace}" : '') : $namespace . ($widgetNamespace !== '' ? "\\{$widgetNamespace}" : ''),
            ]);
        } elseif ($this->option('stats-overview')) {
            $this->copyStubToApp('StatsOverviewWidget', $path, [
                'class' => $widgetClass,
                'namespace' => filled($resource) ? "{$resourceNamespace}\\{$resource}\\Widgets" . ($widgetNamespace !== '' ? "\\{$widgetNamespace}" : '') : $namespace . ($widgetNamespace !== '' ? "\\{$widgetNamespace}" : ''),
            ]);
        } else {
            $this->copyStubToApp('Widget', $path, [
                'class' => $widgetClass,
                'namespace' => filled($resource) ? "{$resourceNamespace}\\{$resource}\\Widgets" . ($widgetNamespace !== '' ? "\\{$widgetNamespace}" : '') : $namespace . ($widgetNamespace !== '' ? "\\{$widgetNamespace}" : ''),
                'view' => $namespaced_view,
            ]);

            $this->copyStubToApp('WidgetView', $viewPath);
        }

        $this->info("Successfully created {$widget}!");

        if ($resource !== null) {
            $this->info("Make sure to register the widget in `{$resourceClass}::getWidgets()`, and then again in `getHeaderWidgets()` or `getFooterWidgets()` of any `{$resourceClass}` page.");
        }

        return static::SUCCESS;
    }
}
