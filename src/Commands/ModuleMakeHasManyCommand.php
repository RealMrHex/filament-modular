<?php

namespace RealMrHex\FilamentModular\Commands;

class ModuleMakeHasManyCommand extends ModuleMakeRelationManagerCommand
{
    protected $signature = 'module:make-filament-has-many {resource?} {module?} {relationship?} {recordTitleAttribute?} {--attach} {--associate} {--soft-deletes} {--view} {--F|force}';

    public function option($key = null)
    {
        if ('associate' === $key) {
            return true;
        }

        return parent::option($key);
    }
}
