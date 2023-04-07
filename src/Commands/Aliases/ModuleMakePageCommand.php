<?php

namespace RealMrHex\FilamentModular\Commands\Aliases;

use RealMrHex\FilamentModular\Commands;

class ModuleMakePageCommand extends Commands\ModuleMakePageCommand
{
    protected $hidden = true;

    protected $signature = 'filament:module-module-page {name?} {module?} {--R|resource=} {--T|type=} {--F|force}';
}
