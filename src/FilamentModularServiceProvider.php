<?php

namespace RealMrHex\FilamentModular;

use Filament\Pages\Page;
use Filament\PluginServiceProvider as ServiceProvider;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\Resource;
use Filament\Widgets\Widget;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\{Str, Stringable};
use Livewire\Component;
use Nwidart\Modules\Laravel\Module;
use RealMrHex\FilamentModular\Commands\{
    ModuleMakeBelongsToManyCommand,
    ModuleMakeHasManyCommand,
    ModuleMakeHasManyThroughCommand,
    ModuleMakeMorphManyCommand,
    ModuleMakeMorphToManyCommand,
    ModuleMakePageCommand,
    ModuleMakeRelationManagerCommand,
    ModuleMakeResourceCommand,
    ModuleMakeWidgetCommand
};
use ReflectionClass;
use ReflectionException;
use Spatie\LaravelPackageTools\Package;

class FilamentModularServiceProvider extends ServiceProvider
{
    protected array $pathFormats = [];
    protected array $moduleConfig = [];

    /**
     * Configure package services.
     */
    public function configurePackage(Package $package): void
    {
        $this->initializeConfigurations();

        $package
            ->name('filament-modular')
            ->hasConfigFile()
            ->hasCommands($this->getCommands());

        $this->app->booting(fn () => $this->initializeModules());
    }

    /**
     * Initialize package configurations.
     */
    protected function initializeConfigurations(): void
    {
        $this->pathFormats = [
            'livewire' => '%s/'.ltrim(config('filament-modular.livewire.path'), '/'),
            'widgets' => '%s/'.ltrim(config('filament-modular.widgets.path'), '/'),
            'resources' => '%s/'.ltrim(config('filament-modular.resources.path'), '/'),
            'pages' => '%s/'.ltrim(config('filament-modular.pages.path'), '/'),
        ];

        $this->moduleConfig = [
            'namespace' => config('filament-modular.modules.namespace'),
            'livewire_namespace' => ltrim(config('filament-modular.livewire.namespace'), '\\'),
        ];

        $this->loadViewsFrom(
            config('filament-modular.views.path'),
            config('filament-modular.views.namespace')
        );
    }

    /**
     * Get all modular commands including aliases.
     */
    protected function getCommands(): array
    {
        return array_merge(
            $this->getMakeCommands(),
            $this->generateCommandAliases()
        );
    }

    /**
     * Generate command aliases dynamically.
     */
    protected function generateCommandAliases(): array
    {
        return collect($this->getMakeCommands())
            ->map(fn ($command) => 'RealMrHex\\FilamentModular\\Commands\\Aliases\\'.class_basename($command))
            ->filter(fn ($alias) => class_exists($alias))
            ->toArray();
    }

    /**
     * Get all make commands.
     */
    protected function getMakeCommands(): array
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
     * Initialize all enabled modules.
     */
    protected function initializeModules(): void
    {
        collect($this->app['modules']->allEnabled())
            ->each(fn (Module $module) => $this->scanModule($module));
    }

    /**
     * Scan a module for Filament components.
     *
     * @throws FileNotFoundException|ReflectionException
     */
    protected function scanModule(Module $module): void
    {
        $filesystem = app(Filesystem::class);
        $modulePath = $module->getPath();
        $moduleName = $module->getName();

        $moduleDirectory = sprintf($this->pathFormats['livewire'], $modulePath);
        $moduleNamespace = sprintf(
            '%s\\%s\\%s',
            $this->moduleConfig['namespace'],
            $moduleName,
            $this->moduleConfig['livewire_namespace']
        );

        if (!$filesystem->isDirectory($moduleDirectory)) {
            return;
        }

        $this->processModuleFiles($filesystem, $moduleDirectory, $moduleNamespace);
    }

    /**
     * Process all files in a module directory.
     */
    protected function processModuleFiles(
        Filesystem $filesystem,
        string $moduleDirectory,
        string $moduleNamespace
    ): void {
        foreach ($filesystem->allFiles($moduleDirectory) as $file) {
            $fileClass = $this->generateFileClass($file, $moduleNamespace);

            if (!$this->isValidClass($fileClass)) {
                continue;
            }

            $this->registerComponent($fileClass, $file->getPathname(), $moduleDirectory);
        }
    }

    /**
     * Generate fully qualified class name from file.
     */
    protected function generateFileClass(SplFileInfo $file, string $moduleNamespace): string
    {
        return (string) Str::of($moduleNamespace)
            ->append('\\', $file->getRelativePathname())
            ->replace(['/', '.php'], ['\\', '']);
    }

    /**
     * Check if a class is valid for registration.
     */
    protected function isValidClass(string $fileClass): bool
    {
        try {
            $reflection = new ReflectionClass($fileClass);
            return $reflection->isInstantiable() && !$reflection->isAbstract();
        } catch (ReflectionException) {
            return false;
        }
    }

    /**
     * Register a component based on its type.
     */
    protected function registerComponent(string $fileClass, string $filePath, string $moduleDirectory): void
    {
        $filePath = Str::of($filePath);

        match (true) {
            $this->isResource($fileClass, $filePath, $moduleDirectory) => $this->resources[] = $fileClass,
            $this->isPage($fileClass, $filePath, $moduleDirectory) => $this->pages[] = $fileClass,
            $this->isWidget($fileClass, $filePath, $moduleDirectory) => $this->widgets[] = $fileClass,
            $this->isLivewireComponent($fileClass) => $this->registerLivewireComponent($fileClass, $moduleDirectory),
            default => null,
        };
    }

    /**
     * Check if class is a Filament Resource.
     */
    protected function isResource(string $fileClass, Stringable $filePath, string $moduleDirectory): bool
    {
        return $filePath->startsWith(sprintf($this->pathFormats['resources'], $moduleDirectory))
            && is_subclass_of($fileClass, Resource::class);
    }

    /**
     * Check if class is a Filament Page.
     */
    protected function isPage(string $fileClass, Stringable $filePath, string $moduleDirectory): bool
    {
        return $filePath->startsWith(sprintf($this->pathFormats['pages'], $moduleDirectory))
            && is_subclass_of($fileClass, Page::class);
    }

    /**
     * Check if class is a Filament Widget.
     */
    protected function isWidget(string $fileClass, Stringable $filePath, string $moduleDirectory): bool
    {
        return $filePath->startsWith(sprintf($this->pathFormats['widgets'], $moduleDirectory))
            && is_subclass_of($fileClass, Widget::class);
    }

    /**
     * Check if class is a Livewire Component (excluding RelationManager).
     */
    protected function isLivewireComponent(string $fileClass): bool
    {
        return is_subclass_of($fileClass, Component::class)
            && !is_subclass_of($fileClass, RelationManager::class);
    }

    /**
     * Register a Livewire component with its alias.
     */
    protected function registerLivewireComponent(string $fileClass, string $moduleDirectory): void
    {
        $alias = Str::of($fileClass)
            ->after($moduleDirectory.'\\')
            ->replace(['/', '\\'], '.')
            ->prepend('filament.')
            ->explode('.')
            ->map(fn ($part) => Str::kebab($part))
            ->implode('.');

        $this->livewireComponents[$alias] = $fileClass;
    }
}
