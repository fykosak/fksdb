<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\Transition;

use FKSDB\Models\Events\EventDispatchFactory;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\Transitions\Machine\Machine;

trait TransitionComponent
{
    protected EventModel $event;
    protected EventDispatchFactory $eventDispatchFactory;

    public function inject(EventDispatchFactory $eventDispatchFactory): void
    {
        $this->eventDispatchFactory = $eventDispatchFactory;
    }

    /**
     * @throws BadTypeException
     */
    protected function getMachine(): Machine
    {
        return $this->eventDispatchFactory->getEventMachine($this->event);
    }
}
