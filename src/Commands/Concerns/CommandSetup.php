<?php

namespace RealMrHex\FilamentModular\Commands\Concerns;

use Illuminate\Support\Str;

trait CommandSetup
{
    protected string $module_name;
    protected string $module;

    protected string $base;

    protected string $_directory_format;
    protected string $_namespace_format;
    protected string $_module_namespace;

    protected string $module_path;

    protected string $module_directory;
    protected string $module_namespace;

    protected string $_widgets_format;
    protected string $_resources_format;
    protected string $_pages_format;

    protected string $widgets_path;
    protected string $resources_path;
    protected string $pages_path;

    protected string $_widgets_namespace_format;
    protected string $_resources_namespace_format;
    protected string $_pages_namespace_format;

    protected string $widgets_namespace;
    protected string $resources_namespace;
    protected string $pages_namespace;

    protected string $views_namespace;

    protected string $theme_dir;
    protected string $in_module;

    private function init(): void
    {
        $this->module_name = $this->ensureArg('module', 'Module Name (e.g. `User`)');
        $this->module_name = Str::of($this->module_name)->studly()->toString();
        $this->module = null;

        $this->base = config('filament-modular.livewire.path');

        try {
            /**
             * @var \Nwidart\Modules\Facades\Module $module
             */
            $module = app('modules')->findOrFail($this->module_name);
        } catch (\Throwable $exception) {
            $this->error('module not found');

            return static::INVALID;
        }

        $this->_directory_format = '%s/'.Str::replaceFirst('/', '', config('filament-modular.livewire.path'));
        $this->_namespace_format = '%s\\%s\\'.Str::replaceFirst('\\', '', config('filament-modular.livewire.namespace'));
        $this->_module_namespace = config('filament-modular.modules.namespace');

        $this->module_path = $module->getPath();
        $this->module_name = $module->getName();

        $this->module_directory = sprintf($this->_directory_format, $this->module_path);
        $this->module_namespace = sprintf($this->_namespace_format, $this->_module_namespace, $this->module_name);

        $this->_widgets_format = '%s/'.Str::replaceFirst('/', '', config('filament-modular.widgets.path'));
        $this->_resources_format = '%s/'.Str::replaceFirst('/', '', config('filament-modular.resources.path'));
        $this->_pages_format = '%s/'.Str::replaceFirst('/', '', config('filament-modular.pages.path'));

        $this->widgets_path = sprintf($this->_widgets_format, $this->module_directory);
        $this->resources_path = sprintf($this->_resources_format, $this->module_directory);
        $this->pages_path = sprintf($this->_pages_format, $this->module_directory);

        $this->_widgets_namespace_format = '%s\\'.Str::replaceFirst('\\', '', config('filament-modular.widgets.namespace'));
        $this->_resources_namespace_format = '%s\\'.Str::replaceFirst('\\', '', config('filament-modular.resources.namespace'));
        $this->_pages_namespace_format = '%s\\'.Str::replaceFirst('\\', '', config('filament-modular.pages.namespace'));

        $this->widgets_namespace = sprintf($this->_widgets_namespace_format, $this->module_namespace);
        $this->resources_namespace = sprintf($this->_resources_namespace_format, $this->module_namespace);
        $this->pages_namespace = sprintf($this->_pages_namespace_format, $this->module_namespace);

        $this->views_namespace = config('filament-modular.views.namespace');

        $this->theme_dir = config('filament-modular.views.path');
        $this->in_module = config('filament-modular.views.in_module');
    }
}
