<?php

namespace FKSDB\EventPayment\SymbolGenerator;

use FKSDB\EventPayment\SymbolGenerator\Generators\Fyziklani13Generator;
use FKSDB\ORM\ModelEvent;
use Nette\NotImplementedException;

class SymbolGeneratorFactory {
    /**
     * @var \ServicePayment;
     */
    protected $serviceEventPayment;

    public function __construct(\ServicePayment $serviceEventPayment) {
        $this->serviceEventPayment = $serviceEventPayment;
    }

    public function createGenerator(ModelEvent $event): AbstractSymbolGenerator {
        if ($event->event_type_id === 1 && $event->event_year = 13) {
            return new Fyziklani13Generator($this->serviceEventPayment);
        }
        throw new NotImplementedException(\sprintf(_('Event %s nemá nastavený generátor platieb'), $event->name));
    }
}
