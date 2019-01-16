<?php

namespace FKSDB\Payment\SymbolGenerator;

use FKSDB\ORM\ModelEvent;
use FKSDB\Payment\SymbolGenerator\Generators\Fyziklani13Generator;
use Nette\NotImplementedException;

class SymbolGeneratorFactory {
    /**
     * @var \ServicePayment;
     */
    protected $servicePayment;

    public function __construct(\ServicePayment $servicePayment) {
        $this->servicePayment = $servicePayment;
    }

    /**
     * @param ModelEvent $event
     * @return AbstractSymbolGenerator
     */
    public function createGenerator(ModelEvent $event): AbstractSymbolGenerator {
        if ($event->event_type_id === 1 && $event->event_year = 13) {
            return new Fyziklani13Generator($this->servicePayment);
        }
        throw new NotImplementedException(\sprintf(_('Event %s nemá nastavený generátor platieb'), $event->name), 501);
    }
}
