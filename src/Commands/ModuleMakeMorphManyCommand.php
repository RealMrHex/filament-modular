<?php

namespace RealMrHex\FilamentModular\Commands;

class ModuleMakeMorphManyCommand extends ModuleMakeRelationManagerCommand
{
    protected $signature = 'module:make-filament-morph-many {resource?} {module?} {relationship?} {recordTitleAttribute?} {--attach} {--associate} {--soft-deletes} {--view} {--F|force}';

    public function option($key = null)
    {
        if ($key === 'associate') {
            return true;
        }

        return parent::option($key);
    }
}
