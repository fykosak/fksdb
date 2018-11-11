<?php

namespace FKSDB\EventPayment\SymbolGenerator;

use FKSDB\EventPayment\SymbolGenerator\Generators\Fyziklani13Generator;
use FKSDB\ORM\ModelEvent;

class SymbolGeneratorFactory {
    /**
     * @var \ServiceEventPayment;
     */
    protected $serviceEventPayment;

    public function __construct(\ServiceEventPayment $serviceEventPayment) {
        $this->serviceEventPayment = $serviceEventPayment;
    }

    public function createGenerator(ModelEvent $event): AbstractSymbolGenerator {
        if ($event->event_type_id === 1 && $event->event_year = 13) {
            return new Fyziklani13Generator($this->serviceEventPayment);
        }
    }
}
