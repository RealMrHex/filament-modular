<?php

namespace RealMrHex\FilamentModular;

use Filament\Pages\Page;
use Filament\PluginServiceProvider as ServiceProvider;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\Resource;
use Filament\Widgets\Widget;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Livewire\Component;
use Nwidart\Modules\Laravel\Module;
use RealMrHex\FilamentModular\Commands\ModuleMakeBelongsToManyCommand;
use RealMrHex\FilamentModular\Commands\ModuleMakeHasManyCommand;
use RealMrHex\FilamentModular\Commands\ModuleMakeHasManyThroughCommand;
use RealMrHex\FilamentModular\Commands\ModuleMakeMorphManyCommand;
use RealMrHex\FilamentModular\Commands\ModuleMakeMorphToManyCommand;
use RealMrHex\FilamentModular\Commands\ModuleMakePageCommand;
use RealMrHex\FilamentModular\Commands\ModuleMakeRelationManagerCommand;
use RealMrHex\FilamentModular\Commands\ModuleMakeResourceCommand;
use RealMrHex\FilamentModular\Commands\ModuleMakeWidgetCommand;
use ReflectionClass;
use Spatie\LaravelPackageTools\Package;

class FilamentModularServiceProvider extends ServiceProvider
{
    /**
     * Configure package services.
     *
     * @param Package $package
     *
     * @return void
     */
    public function configurePackage(Package $package): void
    {
        $package
            ->name('filament-modular')
            ->hasConfigFile()
            ->hasCommands($this->getCommands());

        $this->app->booting(fn() => $this->initModules());
        $this->loadViews();
    }

    /**
     * Load Views
     *
     * @return void
     */
    private function loadViews(): void
    {
        $path = config('filament-modular.views.path');
        $namespace = config('filament-modular.views.namespace');
        $this->loadViewsFrom($path, $namespace);
    }

    /**
     * Get modular commands
     *
     * @return string[]
     */
    protected function getCommands(): array
    {
        $commands = $this->getMakeCommands();

        $aliases = [];

        foreach ($commands as $command):
            $class = 'RealMrHex\\FilamentModular\\Commands\\Aliases\\' . class_basename($command);

            if (class_exists($class))
                $aliases[] = $class;
        endforeach;

        return array_merge($commands, $aliases);
    }

    /**
     * Get make:x commands
     *
     * @return string[]
     */
    private function getMakeCommands(): array
    {
        return [
            ModuleMakeBelongsToManyCommand::class,
            ModuleMakeHasManyCommand::class,
            ModuleMakeHasManyThroughCommand::class,
            ModuleMakeMorphManyCommand::class,
            ModuleMakeMorphToManyCommand::class,
            ModuleMakePageCommand::class,
            ModuleMakeRelationManagerCommand::class,
            ModuleMakeResourceCommand::class,
            ModuleMakeWidgetCommand::class,
        ];
    }

    /**
     * Fetch all active modules and initialize them.
     *
     * @return void
     */
    private function initModules(): void
    {
        /**
         * List of all enabled modules
         *
         * @var Module[] $modules
         */
        $modules = $this->app['modules']->allEnabled();

        foreach ($modules as $module)
            $this->scanModule($module);
    }

    /**
     * Scan current module for finding pages, widgets, resources, etc.
     *
     * @param Module $module
     *
     * @return void
     */
    private function scanModule(Module $module): void
    {
        /**
         * Filesystem instance
         *
         * @var Filesystem $filesystem
         */
        $filesystem = resolve(Filesystem::class);

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

        // Return if module directory not exists
        if (!$filesystem->isDirectory($module_directory))
            return;

        // Loop on directory files
        foreach ($filesystem->allFiles($module_directory) as $file)
        {
            // Generate file class
            $fileClass = Str::of($module_namespace)
                            ->append('\\', $file->getRelativePathname())
                            ->replace(['/', '.php'], ['\\', ''])
                            ->toString();

            // Continue in case of "abstract class" or when class "is not exists"
            if (!class_exists($fileClass) || (new ReflectionClass($fileClass))->isAbstract())
                continue;

            // Get the file path
            $filePath = Str::of($module_directory . '/' . $file->getRelativePathname());


            // Check for Resource instance
            if ($filePath->startsWith($resources_path) && is_subclass_of($fileClass, Resource::class)):
                $this->resources[] = $fileClass;
                continue;
            endif;

            // Check for Page instance
            if ($filePath->startsWith($pages_path) && is_subclass_of($fileClass, Page::class)):
                $this->pages[] = $fileClass;
                continue;
            endif;

            // Check for Widget instance
            if ($filePath->startsWith($widgets_path) && is_subclass_of($fileClass, Widget::class)):
                $this->widgets[] = $fileClass;
                continue;
            endif;

            // Continue on RelationManger subclasses
            if (is_subclass_of($fileClass, RelationManager::class))
                continue;

            // Continue if it's not a Livewire Component
            if (!is_subclass_of($fileClass, Component::class))
                continue;

            // Generate the Alias
            $_livewire_alias = Str::of($fileClass)
                                  ->after($module_namespace . '\\')
                                  ->replace(['/', '\\'], '.')
                                  ->prepend('filament.')
                                  ->explode('.')
                                  ->map([Str::class, 'kebab'])
                                  ->implode('.');

            // Register livewire component via its alias
            $this->livewireComponents[$_livewire_alias] = $fileClass;
        }
    }
}
