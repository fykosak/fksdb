<?php

namespace FKSDB\Payment\SymbolGenerator;

use FKSDB\ORM\Models\ModelEvent;
use FKSDB\ORM\Services\ServicePayment;
use FKSDB\Payment\SymbolGenerator\Generators\AbstractSymbolGenerator;
use Nette\DI\Container;
use Nette\NotImplementedException;
use function sprintf;

/**
 * Class SymbolGeneratorFactory
 * @package FKSDB\Payment\SymbolGenerator
 */
class SymbolGeneratorFactory {
    /**
     * @var ServicePayment;
     */
    protected $servicePayment;
    /**
     * @var Container
     */
    private $context;

    /**
     * SymbolGeneratorFactory constructor.
     * @param ServicePayment $servicePayment
     * @param Container $context
     */
    public function __construct(ServicePayment $servicePayment, Container $context) {
        $this->servicePayment = $servicePayment;
        $this->context = $context;
    }

    /**
     * @param ModelEvent $event
     * @return AbstractSymbolGenerator
     * @throws \Exception
     */
    public function createGenerator(ModelEvent $event): AbstractSymbolGenerator {
        $service = $this->context->getService('payment.symbolGenerator.' . $event->event_id);
        if ($service instanceof AbstractSymbolGenerator) {
            return $service;
        }
        throw new NotImplementedException(sprintf(_('Event %s nemá nastavený generátor platieb'), $event->name), 501);
    }
}
