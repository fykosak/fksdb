<?php

namespace FKSDB\Model\Payment\Transition;

use FKSDB\Model\ORM\Models\ModelEvent;
use FKSDB\Model\ORM\Models\ModelPayment;
use FKSDB\Model\ORM\Services\ServiceEvent;
use FKSDB\Model\ORM\Services\ServicePayment;
use FKSDB\Model\Payment\PriceCalculator\PriceCalculator;
use FKSDB\Model\Transitions\ITransitionsDecorator;
use FKSDB\Model\Transitions\Machine;
use Nette\Database\Context;

/**
 * Class PaymentMachine
 * @author Michal Červeňák <miso@fykos.cz>
 */
class PaymentMachine extends Machine\Machine {

    private PriceCalculator $priceCalculator;
    private ModelEvent $event;
    private ServiceEvent $serviceEvent;
    private array $scheduleGroupTypes;

    public function __construct(Context $connection, ServicePayment $servicePayment, ServiceEvent $serviceEvent) {
        parent::__construct($connection, $servicePayment);
        $this->serviceEvent = $serviceEvent;
    }

    public function decorateTransitions(ITransitionsDecorator $decorator): void {
        $decorator->decorate($this);
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

    public function setPriceCalculator(PriceCalculator $priceCalculator): void {
        $this->priceCalculator = $priceCalculator;
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
