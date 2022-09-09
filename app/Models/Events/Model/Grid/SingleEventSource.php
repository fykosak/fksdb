<?php

declare(strict_types=1);

namespace FKSDB\Models\Events\Model\Grid;

use FKSDB\Models\Events\Exceptions\ConfigurationNotFoundException;
use FKSDB\Models\Events\Model\Holder\BaseHolder;
use FKSDB\Models\Expressions\NeonSchemaException;
use FKSDB\Models\Events\EventDispatchFactory;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\EventParticipantModel;
use Fykosak\NetteORM\TypedSelection;
use Nette\Database\Table\Selection;
use Nette\DI\Container;
use Nette\SmartObject;

class SingleEventSource implements HolderSource
{
    use SmartObject;

    private EventModel $event;
    private Container $container;
    private EventDispatchFactory $eventDispatchFactory;
    private Selection $primarySelection;
    private BaseHolder $dummyHolder;
    private ?TypedSelection $primaryModels = null;
    /** @var BaseHolder[] */
    private array $holders = [];

    /**
     * @throws NeonSchemaException
     * @throws ConfigurationNotFoundException
     */
    public function __construct(EventModel $event, Container $container, EventDispatchFactory $eventDispatchFactory)
    {
        $this->event = $event;
        $this->container = $container;
        $this->eventDispatchFactory = $eventDispatchFactory;
        $this->dummyHolder = $eventDispatchFactory->getDummyHolder($this->event);
        $this->primarySelection = $this->dummyHolder
            ->service
            ->getTable()
            ->where('event_participant.event_id', $this->event->getPrimary());
    }

    public function getEvent(): EventModel
    {
        return $this->event;
    }

    public function getDummyHolder(): BaseHolder
    {
        return $this->dummyHolder;
    }

    private function loadData(): void
    {
        $this->primaryModels = $this->primarySelection;
        $this->holders = [];
    }

    /**
     * @throws NeonSchemaException
     * @throws ConfigurationNotFoundException
     */
    private function createHolders(): void
    {
        /** @var EventParticipantModel $model */
        foreach ($this->primarySelection as $model) {
            $holder = $this->eventDispatchFactory->getDummyHolder($this->event);
            $holder->setModel($model);
            $this->holders[$model->getPrimary()] = $holder;
        }
    }

    /**
     * @return BaseHolder[]
     * @throws NeonSchemaException
     */
    public function getHolders(): array
    {
        if (!isset($this->primaryModels)) {
            $this->loadData();
            $this->createHolders();
        }
        return $this->holders;
    }
}
