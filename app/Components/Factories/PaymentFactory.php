<?php

namespace FKSDB\Components\Factories;

use FKSDB\Components\Controls\Payment\DetailControl;
use FKSDB\Components\Forms\Controls\Autocomplete\PersonProvider;
use FKSDB\Components\Forms\Controls\Payment\SelectForm;
use FKSDB\Components\Forms\Factories\PersonFactory;
use FKSDB\Components\Forms\Factories\TableReflectionFactory;
use FKSDB\Components\Grids\Payment\OrgPaymentGrid;
use FKSDB\ORM\Models\ModelEvent;
use FKSDB\ORM\Models\ModelPayment;
use FKSDB\ORM\Services\ServiceEventPersonAccommodation;
use FKSDB\ORM\Services\ServicePayment;
use FKSDB\ORM\Services\ServicePaymentAccommodation;
use FKSDB\Payment\Transition\PaymentMachine;
use Nette\Localization\ITranslator;

/**
 * Class PaymentFactory
 * @package FKSDB\Components\Factories
 */
class PaymentFactory {
    /**
     * @var ITranslator
     */
    private $translator;
    /**
     * @var ServicePayment
     */
    private $servicePayment;

    /**
     * @var PersonFactory
     */
    private $personFactory;
    /**
     * @var PersonProvider
     */
    private $personProvider;
    /**
     * @var ServiceEventPersonAccommodation
     */
    private $serviceEventPersonAccommodation;
    /**
     * @var ServicePaymentAccommodation
     */
    private $servicePaymentAccommodation;
    /**
     * @var TableReflectionFactory
     */
    private $tableReflectionFactory;

    /**
     * PaymentFactory constructor.
     * @param TableReflectionFactory $tableReflectionFactory
     * @param ServicePaymentAccommodation $servicePaymentAccommodation
     * @param PersonFactory $personFactory
     * @param PersonProvider $personProvider
     * @param ServiceEventPersonAccommodation $serviceEventPersonAccommodation
     * @param ITranslator $translator
     * @param ServicePayment $servicePayment
     */
    public function __construct(TableReflectionFactory $tableReflectionFactory, ServicePaymentAccommodation $servicePaymentAccommodation, PersonFactory $personFactory, PersonProvider $personProvider, ServiceEventPersonAccommodation $serviceEventPersonAccommodation, ITranslator $translator, ServicePayment $servicePayment) {
        $this->tableReflectionFactory = $tableReflectionFactory;
        $this->translator = $translator;
        $this->servicePayment = $servicePayment;
        $this->personFactory = $personFactory;
        $this->personProvider = $personProvider;
        $this->serviceEventPersonAccommodation = $serviceEventPersonAccommodation;
        $this->servicePaymentAccommodation = $servicePaymentAccommodation;
    }

    /**
     * @param ModelEvent $event
     * @return OrgPaymentGrid
     */
    public function createOrgGrid(ModelEvent $event): OrgPaymentGrid {
        return new OrgPaymentGrid($this->servicePayment, $event, $this->tableReflectionFactory);
    }

    /**
     * @param ModelPayment $modelPayment
     * @param PaymentMachine $machine
     * @return DetailControl
     */
    public function createDetailControl(ModelPayment $modelPayment, PaymentMachine $machine): DetailControl {
        return new DetailControl($this->translator, $machine, $modelPayment, $this->tableReflectionFactory);
    }

    /**
     * @param \FKSDB\ORM\Models\ModelEvent $event
     * @param bool $isOrg
     * @param PaymentMachine $machine
     * @return SelectForm
     */
    public function creteForm(ModelEvent $event, bool $isOrg, PaymentMachine $machine): SelectForm {
        return new SelectForm(
            $event,
            $isOrg,
            $this->translator,
            $this->servicePayment,
            $machine,
            $this->personFactory,
            $this->personProvider,
            $this->serviceEventPersonAccommodation,
            $this->servicePaymentAccommodation
        );
    }
}
