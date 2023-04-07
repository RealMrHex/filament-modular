<?php

namespace RealMrHex\FilamentModular\Commands\Aliases;

use RealMrHex\FilamentModular\Commands;

class ModuleMakeMorphToManyCommand extends Commands\ModuleMakeMorphToManyCommand
{
    protected $hidden = true;

    protected $signature = 'filament:module-morph-to-many {resource?} {module?} {relationship?} {recordTitleAttribute?} {--attach} {--associate} {--soft-deletes} {--view} {--F|force}';
}
