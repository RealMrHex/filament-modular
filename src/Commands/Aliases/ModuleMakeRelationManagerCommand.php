<?php

namespace RealMrHex\FilamentModular\Commands\Aliases;

use RealMrHex\FilamentModular\Commands;

class ModuleMakeRelationManagerCommand extends Commands\ModuleMakeRelationManagerCommand
{
    protected $hidden = true;

    protected $signature = 'filament:module-relation-manager {resource?} {module?} {relationship?} {recordTitleAttribute?} {--attach} {--associate} {--soft-deletes} {--view} {--F|force}';
}
