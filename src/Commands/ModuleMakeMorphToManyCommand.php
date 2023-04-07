<?php

namespace RealMrHex\FilamentModular\Commands;

class ModuleMakeMorphToManyCommand extends ModuleMakeRelationManagerCommand
{
    protected $signature = 'module:make-filament-morph-to-many {resource?} {module?} {relationship?} {recordTitleAttribute?} {--attach} {--associate} {--soft-deletes} {--view} {--F|force}';

    public function option($key = null)
    {
        if ($key === 'attach') {
            return true;
        }

        return parent::option($key);
    }
}
