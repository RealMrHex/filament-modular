<?php

namespace RealMrHex\FilamentModular\Commands\Aliases;

use RealMrHex\FilamentModular\Commands;

class ModuleMakeHasManyThroughCommand extends Commands\ModuleMakeHasManyThroughCommand
{
    protected $hidden = true;

    protected $signature = 'filament:module-has-many-through {resource?} {module?} {relationship?} {recordTitleAttribute?} {--attach} {--associate} {--soft-deletes} {--view} {--F|force}';
}
