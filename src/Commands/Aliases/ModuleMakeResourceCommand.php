<?php

namespace RealMrHex\FilamentModular\Commands\Aliases;

use RealMrHex\FilamentModular\Commands;

class ModuleMakeResourceCommand extends Commands\ModuleMakeResourceCommand
{
    protected $hidden = true;

    protected $signature = 'filament:module-resource {name?} {module?} {--soft-deletes} {--view} {--G|generate} {--S|simple} {--F|force}';
}
