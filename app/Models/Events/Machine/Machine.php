<?php

declare(strict_types=1);

namespace FKSDB\Models\Events\Machine;

class Machine
{
    public BaseMachine $primaryMachine;

    public function addBaseMachine(BaseMachine $baseMachine): void
    {
        $this->primaryMachine = $baseMachine;
    }
}
