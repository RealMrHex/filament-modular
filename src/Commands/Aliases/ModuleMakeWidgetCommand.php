<?php

namespace RealMrHex\FilamentModular\Commands\Aliases;

use RealMrHex\FilamentModular\Commands;

class ModuleMakeWidgetCommand extends Commands\ModuleMakeWidgetCommand
{
    protected $hidden = true;

    protected $signature = 'filament:module-widget {name?} {module?} {--R|resource=} {--C|chart} {--T|table} {--S|stats-overview} {--F|force}';
}
