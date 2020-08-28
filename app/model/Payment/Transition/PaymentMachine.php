<?php

namespace FKSDB\Payment\Transition;

use FKSDB\ORM\Models\ModelEvent;
use FKSDB\ORM\Models\ModelPayment;
use FKSDB\ORM\Services\ServiceEvent;
use FKSDB\ORM\Services\ServicePayment;
use FKSDB\Payment\PriceCalculator\PriceCalculator;
use FKSDB\Payment\SymbolGenerator\Generators\AbstractSymbolGenerator;
use FKSDB\Transitions\AbstractTransitionsGenerator;
use FKSDB\Transitions\Machine;
use Nette\Database\Context;

/**
 * Class PaymentMachine
 * @author Michal Červeňák <miso@fykos.cz>
 */
class PaymentMachine extends Machine {

    private PriceCalculator $priceCalculator;

    private AbstractSymbolGenerator $symbolGenerator;

    private ModelEvent $event;

    private ServiceEvent $serviceEvent;

    private array $scheduleGroupTypes;

    /**
     * PaymentMachine constructor.
     * @param Context $connection
     * @param ServicePayment $servicePayment
     * @param ServiceEvent $serviceEvent
     */
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
