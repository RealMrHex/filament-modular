<?php

namespace RealMrHex\FilamentModular\Commands;

use Filament\Support\Commands\Concerns\CanManipulateFiles;
use Filament\Support\Commands\Concerns\CanValidateInput;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Nwidart\Modules\Traits\ModuleCommandTrait;
use RealMrHex\FilamentModular\Commands\Concerns\CommandSetup;
use RealMrHex\FilamentModular\Commands\Concerns\InteractsWithFileNames;

class ModuleMakeWidgetCommand extends Command
{
    use CommandSetup;
    use CanManipulateFiles;
    use CanValidateInput;
    use InteractsWithFileNames;
    use ModuleCommandTrait;

    protected $description = 'Creates a Filament widget class.';

    protected $signature = 'module:make-filament-widget {name?} {module?} {--R|resource=} {--C|chart} {--T|table} {--S|stats-overview} {--F|force}';

    public function handle(): int
    {
        $this->init();

        $path = $this->widgets_path;
        $resourcePath = $this->resources_path;
        $namespace = $this->widgets_namespace;
        $resourceNamespace = $this->resources_namespace;

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
        }

        $view = Str::of($widget)->prepend(
            (string) Str::of(null === $resource ? "{$namespace}\\" : "{$resourceNamespace}\\{$resource}\\widgets\\")
                ->replace('App\\', '')
        )
            ->replace('\\', '/')
            ->replace('Modules/', '')
            ->explode('/')
            ->map(fn ($segment) => Str::lower(Str::kebab($segment)))
            ->implode('.');

        $path = (string) Str::of($widget)
            ->prepend('/')
            ->prepend(null === $resource ? $path : "{$resourcePath}\\{$resource}\\Widgets\\")
            ->replace('\\', '/')
            ->replace('//', '/')
            ->append('.php');

        if ($this->in_module) {
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
                ->prepend($this->theme_dir)
                ->append('.blade.php')
                ->replace('modules/', '')
                ->toString();
            $namespaced_view = "$this->views_namespace::$view";
        }

        if (
            !$this->option('force') && $this->checkForCollision([
                $path,
                ($this->option('stats-overview') || $this->option('chart')) ?: $viewPath,
            ])
        ) {
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
                'namespace' => filled($resource) ? "{$resourceNamespace}\\{$resource}\\Widgets".('' !== $widgetNamespace ? "\\{$widgetNamespace}" : '') : $namespace.('' !== $widgetNamespace ? "\\{$widgetNamespace}" : ''),
                'chart' => Str::studly($chart),
            ]);
        } elseif ($this->option('table')) {
            $this->copyStubToApp('TableWidget', $path, [
                'class' => $widgetClass,
                'namespace' => filled($resource) ? "{$resourceNamespace}\\{$resource}\\Widgets".('' !== $widgetNamespace ? "\\{$widgetNamespace}" : '') : $namespace.('' !== $widgetNamespace ? "\\{$widgetNamespace}" : ''),
            ]);
        } elseif ($this->option('stats-overview')) {
            $this->copyStubToApp('StatsOverviewWidget', $path, [
                'class' => $widgetClass,
                'namespace' => filled($resource) ? "{$resourceNamespace}\\{$resource}\\Widgets".('' !== $widgetNamespace ? "\\{$widgetNamespace}" : '') : $namespace.('' !== $widgetNamespace ? "\\{$widgetNamespace}" : ''),
            ]);
        } else {
            $this->copyStubToApp('Widget', $path, [
                'class' => $widgetClass,
                'namespace' => filled($resource) ? "{$resourceNamespace}\\{$resource}\\Widgets".('' !== $widgetNamespace ? "\\{$widgetNamespace}" : '') : $namespace.('' !== $widgetNamespace ? "\\{$widgetNamespace}" : ''),
                'view' => $namespaced_view,
            ]);

            $this->copyStubToApp('WidgetView', $viewPath);
        }

        $this->info("Successfully created {$widget}!");

        if (null !== $resource) {
            $this->info("Make sure to register the widget in `{$resourceClass}::getWidgets()`, and then again in `getHeaderWidgets()` or `getFooterWidgets()` of any `{$resourceClass}` page.");
        }

        return static::SUCCESS;
    }
}
