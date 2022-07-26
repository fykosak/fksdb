<?php

declare(strict_types=1);

namespace FKSDB\Components\Forms\Containers;

use FKSDB\Components\Forms\Containers\Models\ContainerWithOptions;
use FKSDB\Models\Exceptions\NotImplementedException;
use FKSDB\Models\ORM\Models\Schedule\ModelPersonSchedule;
use FKSDB\Models\ORM\Services\Schedule\ServicePersonSchedule;
use FKSDB\Models\Transitions\Machine\PaymentMachine;
use Nette\DI\Container;

class PersonPaymentContainer extends ContainerWithOptions
{
    private ServicePersonSchedule $servicePersonSchedule;
    private PaymentMachine $machine;
    private bool $showAll;

    /**
     * @throws NotImplementedException
     */
    public function __construct(Container $container, PaymentMachine $machine, bool $showAll = true)
    {
        parent::__construct($container);
        $this->machine = $machine;
        $this->showAll = $showAll;
        $this->configure();
    }

    final public function injectServicePersonSchedule(ServicePersonSchedule $servicePersonSchedule): void
    {
        $this->servicePersonSchedule = $servicePersonSchedule;
    }

    /**
     * @throws NotImplementedException
     * @throws \Exception
     */
    protected function configure(): void
    {
        $query = $this->servicePersonSchedule->getTable()
            ->where('schedule_item.schedule_group.event_id', $this->machine->event->event_id);
        if (count($this->machine->scheduleGroupTypes)) {
            $query->where('schedule_item.schedule_group.schedule_group_type IN', $this->machine->scheduleGroupTypes);
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
                    $container->setOption('label', $model->person->getFullName());
                    $lastPersonId = $model->person_id;
                }
                $container->addCheckbox(
                    (string)$model->person_schedule_id,
                    $model->getLabel()
                    . ' ('
                    . $model->schedule_item->getPrice()->__toString()
                    . ')'
                );
            }
        }
    }
}
