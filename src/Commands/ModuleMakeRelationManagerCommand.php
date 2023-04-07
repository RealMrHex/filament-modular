<?php

namespace RealMrHex\FilamentModular\Commands;

use Filament\Support\Commands\Concerns\CanIndentStrings;
use Filament\Support\Commands\Concerns\CanManipulateFiles;
use Filament\Support\Commands\Concerns\CanValidateInput;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Nwidart\Modules\Laravel\Module;
use Nwidart\Modules\Traits\ModuleCommandTrait;
use RealMrHex\FilamentModular\Commands\Concerns\InteractsWithFileNames;

class ModuleMakeRelationManagerCommand extends Command
{
    use CanIndentStrings;
    use CanManipulateFiles;
    use CanValidateInput;
    use InteractsWithFileNames;
    use ModuleCommandTrait;

    protected $description = 'Creates a Filament relation manager class for a resource.';

    protected $signature = 'module:make-filament-relation-manager {resource?} {module?} {relationship?} {recordTitleAttribute?} {--attach} {--associate} {--soft-deletes} {--view} {--F|force}';

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

        $path = $resources_path;
        $namespace = $resources_namespace;

        $resource = (string) Str::of($this->argument('resource') ?? $this->askRequired('Resource (e.g. `DepartmentResource`)', 'resource'))
            ->studly()
            ->trim('/')
            ->trim('\\')
            ->trim(' ')
            ->replace('/', '\\');

        if (! Str::of($resource)->endsWith('Resource')) {
            $resource .= 'Resource';
        }

        $relationship = (string) Str::of($this->argument('relationship') ?? $this->askRequired('Relationship (e.g. `members`)', 'relationship'))
            ->trim(' ');
        $managerClass = (string) Str::of($relationship)
            ->studly()
            ->append('RelationManager');

        $recordTitleAttribute = (string) Str::of($this->argument('recordTitleAttribute') ?? $this->askRequired('Title attribute (e.g. `name`)', 'title attribute'))
            ->trim(' ');

        $path = (string) Str::of($managerClass)
            ->prepend("{$resourcePath}/{$resource}/RelationManagers/")
            ->replace('\\', '/')
            ->append('.php');

        if (! $this->option('force') && $this->checkForCollision([
            $path,
        ])) {
            return static::INVALID;
        }

        $tableHeaderActions = [];

        $tableHeaderActions[] = 'Tables\Actions\CreateAction::make(),';

        if ($this->option('associate')) {
            $tableHeaderActions[] = 'Tables\Actions\AssociateAction::make(),';
        }

        if ($this->option('attach')) {
            $tableHeaderActions[] = 'Tables\Actions\AttachAction::make(),';
        }

        $tableHeaderActions = implode(PHP_EOL, $tableHeaderActions);

        $tableActions = [];

        if ($this->option('view')) {
            $tableActions[] = 'Tables\Actions\ViewAction::make(),';
        }

        $tableActions[] = 'Tables\Actions\EditAction::make(),';

        if ($this->option('associate')) {
            $tableActions[] = 'Tables\Actions\DissociateAction::make(),';
        }

        if ($this->option('attach')) {
            $tableActions[] = 'Tables\Actions\DetachAction::make(),';
        }

        $tableActions[] = 'Tables\Actions\DeleteAction::make(),';

        if ($this->option('soft-deletes')) {
            $tableActions[] = 'Tables\Actions\ForceDeleteAction::make(),';
            $tableActions[] = 'Tables\Actions\RestoreAction::make(),';
        }

        $tableActions = implode(PHP_EOL, $tableActions);

        $tableBulkActions = [];

        if ($this->option('associate')) {
            $tableBulkActions[] = 'Tables\Actions\DissociateBulkAction::make(),';
        }

        if ($this->option('attach')) {
            $tableBulkActions[] = 'Tables\Actions\DetachBulkAction::make(),';
        }

        $tableBulkActions[] = 'Tables\Actions\DeleteBulkAction::make(),';

        $eloquentQuery = '';

        if ($this->option('soft-deletes')) {
            $tableBulkActions[] = 'Tables\Actions\RestoreBulkAction::make(),';
            $tableBulkActions[] = 'Tables\Actions\ForceDeleteBulkAction::make(),';

            $eloquentQuery .= PHP_EOL . PHP_EOL . 'protected function getTableQuery(): Builder';
            $eloquentQuery .= PHP_EOL . '{';
            $eloquentQuery .= PHP_EOL . '    return parent::getTableQuery()';
            $eloquentQuery .= PHP_EOL . '        ->withoutGlobalScopes([';
            $eloquentQuery .= PHP_EOL . '            SoftDeletingScope::class,';
            $eloquentQuery .= PHP_EOL . '        ]);';
            $eloquentQuery .= PHP_EOL . '}';
        }

        $tableBulkActions = implode(PHP_EOL, $tableBulkActions);

        $this->copyStubToApp('RelationManager', $path, [
            'eloquentQuery' => $this->indentString($eloquentQuery, 1),
            'namespace' => "{$resourceNamespace}\\{$resource}\\RelationManagers",
            'managerClass' => $managerClass,
            'recordTitleAttribute' => $recordTitleAttribute,
            'relationship' => $relationship,
            'tableActions' => $this->indentString($tableActions, 4),
            'tableBulkActions' => $this->indentString($tableBulkActions, 4),
            'tableFilters' => $this->indentString(
                $this->option('soft-deletes') ? 'Tables\Filters\TrashedFilter::make()' : '//',
                4,
            ),
            'tableHeaderActions' => $this->indentString($tableHeaderActions, 4),
        ]);

        $this->info("Successfully created {$managerClass}!");

        $this->info("Make sure to register the relation in `{$resource}::getRelations()`.");

        return static::SUCCESS;
    }
}
