<?php

namespace RealMrHex\FilamentModular\Commands;

class ModuleMakeHasManyThroughCommand extends ModuleMakeRelationManagerCommand
{
    protected $signature = 'module:make-filament-has-many-through {resource?} {module?} {relationship?} {recordTitleAttribute?} {--attach} {--associate} {--soft-deletes} {--view} {--F|force}';

    public function option($key = null)
    {
        if ($key === 'associate') {
            return true;
        }

        return parent::option($key);
    }
}
