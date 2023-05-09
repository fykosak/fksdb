<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\Events\Transitions;

use FKSDB\Models\Events\EventDispatchFactory;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\Transitions\Machine\Machine;
use Fykosak\Utils\BaseComponent\BaseComponent;
use Nette\DI\Container;

abstract class TransitionComponent extends BaseComponent
{
    protected EventModel $event;
    protected EventDispatchFactory $eventDispatchFactory;

    public function __construct(Container $container, EventModel $event)
    {
        parent::__construct($container);
        $this->event = $event;
    }

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
