<?php

namespace FKSDB\Components\Forms\Containers;

use FKSDB\Components\Forms\Containers\Models\ContainerWithOptions;
use FKSDB\Models\Exceptions\NotImplementedException;
use FKSDB\Models\ORM\Models\ModelEvent;
use FKSDB\Models\ORM\Models\Schedule\ModelPersonSchedule;
use FKSDB\Models\ORM\Services\Schedule\ServicePersonSchedule;
use Nette\ComponentModel\IContainer;
use Nette\DI\Container;
use Nette\InvalidStateException;

class PersonPaymentContainer extends ContainerWithOptions {

    private bool $isAttached = false;

    private ServicePersonSchedule $servicePersonSchedule;

    private ModelEvent $event;

    private array $groupTypes;

    private bool $showAll;

    public function __construct(Container $container, ModelEvent $event, array $groupTypes, bool $showAll = true) {
        parent::__construct($container);
        $this->event = $event;
        $this->groupTypes = $groupTypes;
        $this->showAll = $showAll;

        $this->monitor(IContainer::class, function () {
            if (!$this->isAttached) {
                $this->configure();
                $this->isAttached = true;
            }
        });
    }

    final public function injectServicePersonSchedule(ServicePersonSchedule $servicePersonSchedule): void {
        $this->servicePersonSchedule = $servicePersonSchedule;
    }

    /**
     * @return void
     * @throws NotImplementedException
     * @throws InvalidStateException
     */
    protected function configure(): void {
        $query = $this->servicePersonSchedule->getTable()
            ->where('schedule_item.schedule_group.event_id', $this->event->event_id);
        if (count($this->groupTypes)) {
            $query->where('schedule_item.schedule_group.schedule_group_type IN', $this->groupTypes);
        }
        $query->order('person.family_name ,person_id');
        $lastPersonId = null;
        $container = null;
        /** @var ModelPersonSchedule $model */
        foreach ($query as $model) {
            if ($this->showAll || !$model->hasActivePayment()) {
                if ($model->person_id !== $lastPersonId) {
                    $container = new ModelContainer();
                    $this->addComponent($container, 'person' . $model->person_id);
                    $container->setOption('label', $model->getPerson()->getFullName());
                    $lastPersonId = $model->person_id;
                }
                $container->addCheckbox($model->person_schedule_id, $model->getLabel());
            }
        }
    }
}
