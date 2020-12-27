<?php

namespace FKSDB\Models\Payment\Transition;

use FKSDB\Models\ORM\Models\ModelEvent;
use FKSDB\Models\ORM\Models\ModelPayment;
use FKSDB\Models\ORM\Services\ServiceEvent;
use FKSDB\Models\ORM\Services\ServicePayment;
use FKSDB\Models\Payment\PriceCalculator\PriceCalculator;
use FKSDB\Models\Payment\SymbolGenerator\Generators\AbstractSymbolGenerator;
use FKSDB\Models\Transitions\AbstractTransitionsGenerator;
use FKSDB\Models\Transitions\Machine;
use Nette\Database\Context;

/**
 * Class PaymentMachine
 * @author Michal Červeňák <miso@fykos.cz>
 */
class PaymentMachine extends Machine\Machine {

    private PriceCalculator $priceCalculator;

    private AbstractSymbolGenerator $symbolGenerator;

    private ModelEvent $event;

    private ServiceEvent $serviceEvent;

    private array $scheduleGroupTypes;

    public function __construct(Context $connection, ServicePayment $servicePayment, ServiceEvent $serviceEvent) {
        parent::__construct($connection, $servicePayment);
        $this->serviceEvent = $serviceEvent;
    }

    public function setTransitions(AbstractTransitionsGenerator $factory): void {
        $factory->createTransitions($this);
    }

    public function setEventId(int $eventId): void {
        $event = $this->serviceEvent->findByPrimary($eventId);
        if (!is_null($event)) {
            $this->event = $event;
        }
    }

    public function setScheduleGroupTypes(array $types): void {
        $this->scheduleGroupTypes = $types;
    }

    public function getScheduleGroupTypes(): array {
        return $this->scheduleGroupTypes;
    }

    public function setSymbolGenerator(AbstractSymbolGenerator $abstractSymbolGenerator): void {
        $this->symbolGenerator = $abstractSymbolGenerator;
    }

    public function setPriceCalculator(PriceCalculator $priceCalculator): void {
        $this->priceCalculator = $priceCalculator;
    }

    public function getSymbolGenerator(): AbstractSymbolGenerator {
        return $this->symbolGenerator;
    }

    public function getPriceCalculator(): PriceCalculator {
        return $this->priceCalculator;
    }

    public function getEvent(): ModelEvent {
        return $this->event;
    }

    public function getCreatingState(): string {
        return ModelPayment::STATE_NEW;
    }
}
