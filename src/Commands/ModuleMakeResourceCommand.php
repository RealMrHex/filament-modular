<?php

namespace RealMrHex\FilamentModular\Commands;

use Filament\Forms\Commands\Concerns\CanGenerateForms;
use Filament\Support\Commands\Concerns\CanIndentStrings;
use Filament\Support\Commands\Concerns\CanManipulateFiles;
use Filament\Support\Commands\Concerns\CanReadModelSchemas;
use Filament\Support\Commands\Concerns\CanValidateInput;
use Filament\Tables\Commands\Concerns\CanGenerateTables;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Nwidart\Modules\Laravel\Module;
use Nwidart\Modules\Traits\ModuleCommandTrait;
use RealMrHex\FilamentModular\Commands\Concerns\InteractsWithFileNames;

class ModuleMakeResourceCommand extends Command
{
    use CanGenerateForms;
    use CanGenerateTables;
    use CanIndentStrings;
    use CanManipulateFiles;
    use CanReadModelSchemas;
    use CanValidateInput;
    use InteractsWithFileNames;
    use ModuleCommandTrait;

    protected $description = 'Creates a Filament resource class and default page classes.';

    protected $signature = 'module:make-filament-resource {name?} {module?} {--soft-deletes} {--view} {--G|generate} {--S|simple} {--F|force}';

    public function handle(): int
    {
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

        $path = $resources_path;
        $namespace = $resources_namespace;

        $model = (string) Str::of($this->argument('name') ?? $this->askRequired('Model (e.g. `BlogPost`)', 'name'))
            ->studly()
            ->beforeLast('Resource')
            ->trim('/')
            ->trim('\\')
            ->trim(' ')
            ->studly()
            ->replace('/', '\\');

        if (blank($model)) {
            $model = 'Resource';
        }

        $modelClass = (string) Str::of($model)->afterLast('\\');
        $modelNamespace = Str::of($model)->contains('\\') ?
            (string) Str::of($model)->beforeLast('\\') :
            '';
        $pluralModelClass = (string) Str::of($modelClass)->pluralStudly();

        $resource = "{$model}Resource";
        $resourceClass = "{$modelClass}Resource";
        $resourceNamespace = $modelNamespace;
        $namespace .= '' !== $resourceNamespace ? "\\{$resourceNamespace}" : '';
        $listResourcePageClass = "List{$pluralModelClass}";
        $manageResourcePageClass = "Manage{$pluralModelClass}";
        $createResourcePageClass = "Create{$modelClass}";
        $editResourcePageClass = "Edit{$modelClass}";
        $viewResourcePageClass = "View{$modelClass}";

        $baseResourcePath =
            (string) Str::of($resource)
                ->prepend('/')
                ->prepend($path)
                ->replace('\\', '/')
                ->replace('//', '/');

        $resourcePath = "{$baseResourcePath}.php";
        $resourcePagesDirectory = "{$baseResourcePath}/Pages";
        $listResourcePagePath = "{$resourcePagesDirectory}/{$listResourcePageClass}.php";
        $manageResourcePagePath = "{$resourcePagesDirectory}/{$manageResourcePageClass}.php";
        $createResourcePagePath = "{$resourcePagesDirectory}/{$createResourcePageClass}.php";
        $editResourcePagePath = "{$resourcePagesDirectory}/{$editResourcePageClass}.php";
        $viewResourcePagePath = "{$resourcePagesDirectory}/{$viewResourcePageClass}.php";

        if (!$this->option('force') && $this->checkForCollision([
            $resourcePath,
            $listResourcePagePath,
            $manageResourcePagePath,
            $createResourcePagePath,
            $editResourcePagePath,
            $viewResourcePagePath,
        ])) {
            return static::INVALID;
        }

        $pages = '';
        $pages .= '\'index\' => Pages\\'.($this->option('simple') ? $manageResourcePageClass : $listResourcePageClass).'::route(\'/\'),';

        if (!$this->option('simple')) {
            $pages .= PHP_EOL."'create' => Pages\\{$createResourcePageClass}::route('/create'),";

            if ($this->option('view')) {
                $pages .= PHP_EOL."'view' => Pages\\{$viewResourcePageClass}::route('/{record}'),";
            }

            $pages .= PHP_EOL."'edit' => Pages\\{$editResourcePageClass}::route('/{record}/edit'),";
        }

        $tableActions = [];

        if ($this->option('view')) {
            $tableActions[] = 'Tables\Actions\ViewAction::make(),';
        }

        $tableActions[] = 'Tables\Actions\EditAction::make(),';

        $relations = '';

        if ($this->option('simple')) {
            $tableActions[] = 'Tables\Actions\DeleteAction::make(),';

            if ($this->option('soft-deletes')) {
                $tableActions[] = 'Tables\Actions\ForceDeleteAction::make(),';
                $tableActions[] = 'Tables\Actions\RestoreAction::make(),';
            }
        } else {
            $relations .= PHP_EOL.'public static function getRelations(): array';
            $relations .= PHP_EOL.'{';
            $relations .= PHP_EOL.'    return [';
            $relations .= PHP_EOL.'        //';
            $relations .= PHP_EOL.'    ];';
            $relations .= PHP_EOL.'}'.PHP_EOL;
        }

        $tableActions = implode(PHP_EOL, $tableActions);

        $tableBulkActions = [];

        $tableBulkActions[] = 'Tables\Actions\DeleteBulkAction::make(),';

        $eloquentQuery = '';

        if ($this->option('soft-deletes')) {
            $tableBulkActions[] = 'Tables\Actions\ForceDeleteBulkAction::make(),';
            $tableBulkActions[] = 'Tables\Actions\RestoreBulkAction::make(),';

            $eloquentQuery .= PHP_EOL.PHP_EOL.'public static function getEloquentQuery(): Builder';
            $eloquentQuery .= PHP_EOL.'{';
            $eloquentQuery .= PHP_EOL.'    return parent::getEloquentQuery()';
            $eloquentQuery .= PHP_EOL.'        ->withoutGlobalScopes([';
            $eloquentQuery .= PHP_EOL.'            SoftDeletingScope::class,';
            $eloquentQuery .= PHP_EOL.'        ]);';
            $eloquentQuery .= PHP_EOL.'}';
        }

        $tableBulkActions = implode(PHP_EOL, $tableBulkActions);

        $this->copyStubToApp('Resource', $resourcePath, [
            'eloquentQuery' => $this->indentString($eloquentQuery, 1),
            'formSchema' => $this->indentString($this->option('generate') ? $this->getResourceFormSchema(
                'App\\Models'.('' !== $modelNamespace ? "\\{$modelNamespace}" : '').'\\'.$modelClass,
            ) : '//', 4),
            'model' => 'Resource' === $model ? 'Resource as ResourceModel' : $model,
            'modelClass' => 'Resource' === $model ? 'ResourceModel' : $modelClass,
            'namespace' => $namespace,
            'pages' => $this->indentString($pages, 3),
            'relations' => $this->indentString($relations, 1),
            'resource' => "{$namespace}\\{$resourceClass}",
            'resourceClass' => $resourceClass,
            'tableActions' => $this->indentString($tableActions, 4),
            'tableBulkActions' => $this->indentString($tableBulkActions, 4),
            'tableColumns' => $this->indentString($this->option('generate') ? $this->getResourceTableColumns(
                'App\Models'.('' !== $modelNamespace ? "\\{$modelNamespace}" : '').'\\'.$modelClass,
            ) : '//', 4),
            'tableFilters' => $this->indentString(
                $this->option('soft-deletes') ? 'Tables\Filters\TrashedFilter::make(),' : '//',
                4,
            ),
        ]);

        if ($this->option('simple')) {
            $this->copyStubToApp('ResourceManagePage', $manageResourcePagePath, [
                'namespace' => "{$namespace}\\{$resourceClass}\\Pages",
                'resource' => "{$namespace}\\{$resourceClass}",
                'resourceClass' => $resourceClass,
                'resourcePageClass' => $manageResourcePageClass,
            ]);
        } else {
            $this->copyStubToApp('ResourceListPage', $listResourcePagePath, [
                'namespace' => "{$namespace}\\{$resourceClass}\\Pages",
                'resource' => "{$namespace}\\{$resourceClass}",
                'resourceClass' => $resourceClass,
                'resourcePageClass' => $listResourcePageClass,
            ]);

            $this->copyStubToApp('ResourcePage', $createResourcePagePath, [
                'baseResourcePage' => 'Filament\\Resources\\Pages\\CreateRecord',
                'baseResourcePageClass' => 'CreateRecord',
                'namespace' => "{$namespace}\\{$resourceClass}\\Pages",
                'resource' => "{$namespace}\\{$resourceClass}",
                'resourceClass' => $resourceClass,
                'resourcePageClass' => $createResourcePageClass,
            ]);

            $editPageActions = [];

            if ($this->option('view')) {
                $this->copyStubToApp('ResourceViewPage', $viewResourcePagePath, [
                    'namespace' => "{$namespace}\\{$resourceClass}\\Pages",
                    'resource' => "{$namespace}\\{$resourceClass}",
                    'resourceClass' => $resourceClass,
                    'resourcePageClass' => $viewResourcePageClass,
                ]);

                $editPageActions[] = 'Actions\ViewAction::make(),';
            }

            $editPageActions[] = 'Actions\DeleteAction::make(),';

            if ($this->option('soft-deletes')) {
                $editPageActions[] = 'Actions\ForceDeleteAction::make(),';
                $editPageActions[] = 'Actions\RestoreAction::make(),';
            }

            $editPageActions = implode(PHP_EOL, $editPageActions);

            $this->copyStubToApp('ResourceEditPage', $editResourcePagePath, [
                'actions' => $this->indentString($editPageActions, 3),
                'namespace' => "{$namespace}\\{$resourceClass}\\Pages",
                'resource' => "{$namespace}\\{$resourceClass}",
                'resourceClass' => $resourceClass,
                'resourcePageClass' => $editResourcePageClass,
            ]);
        }

        $this->info("Successfully created {$resource}!");

        return static::SUCCESS;
    }
}
