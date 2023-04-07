<?php

namespace RealMrHex\FilamentModular\Commands\Aliases;

use RealMrHex\FilamentModular\Commands;

class ModuleMakeHasManyCommand extends Commands\ModuleMakeHasManyCommand
{
    protected $hidden = true;

    protected $signature = 'filament:module-has-many {resource?} {module?} {relationship?} {recordTitleAttribute?} {--attach} {--associate} {--soft-deletes} {--view} {--F|force}';
}
