<?php

namespace RealMrHex\FilamentModular\Commands\Aliases;

use RealMrHex\FilamentModular\Commands;

class ModuleMakeBelongsToManyCommand extends Commands\ModuleMakeBelongsToManyCommand
{
    protected $hidden = true;

    protected $signature = 'filament:module-belongs-to-many {resource?} {module?} {relationship?} {recordTitleAttribute?} {--attach} {--associate} {--soft-deletes} {--view} {--F|force}';
}
