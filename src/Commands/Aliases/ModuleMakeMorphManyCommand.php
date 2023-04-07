<?php

namespace RealMrHex\FilamentModular\Commands\Aliases;

use RealMrHex\FilamentModular\Commands;

class ModuleMakeMorphManyCommand extends Commands\ModuleMakeMorphManyCommand
{
    protected $hidden = true;

    protected $signature = 'filament:module-morph-many {resource?} {module?} {relationship?} {recordTitleAttribute?} {--attach} {--associate} {--soft-deletes} {--view} {--F|force}';
}
