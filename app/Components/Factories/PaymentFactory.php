<?php

namespace FKSDB\Components\Factories;

use FKSDB\Components\Forms\Controls\Payment\SelectForm;
use FKSDB\Components\Forms\Controls\Autocomplete\PersonProvider;
use FKSDB\Components\Controls\Payment\DetailControl;
use FKSDB\Components\Forms\Factories\PersonFactory;
use FKSDB\Components\Grids\Payment\OrgPaymentGrid;
use FKSDB\ORM\ModelEvent;
use FKSDB\ORM\ModelPayment;
use FKSDB\ORM\Services\ServicePaymentAccommodation;
use FKSDB\Payment\Transition\PaymentMachine;
use Nette\Localization\ITranslator;

class PaymentFactory {
    /**
     * @var ITranslator
     */
    private $translator;
    /**
     * @var \ServicePayment
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
     * @var \ServiceEventPersonAccommodation
     */
    private $serviceEventPersonAccommodation;
    /**
     * @var ServicePaymentAccommodation
     */
    private $servicePaymentAccommodation;


    public function __construct(ServicePaymentAccommodation $servicePaymentAccommodation, PersonFactory $personFactory, PersonProvider $personProvider, \ServiceEventPersonAccommodation $serviceEventPersonAccommodation, ITranslator $translator, \ServicePayment $servicePayment) {
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
        return new OrgPaymentGrid($this->servicePayment, $event);
    }

    /**
     * @param ModelPayment $modelPayment
     * @param PaymentMachine $machine
     * @return DetailControl
     */
    public function createDetailControl(ModelPayment $modelPayment, PaymentMachine $machine): DetailControl {
        return new DetailControl($this->translator, $machine, $modelPayment);
    }

    /**
     * @param ModelEvent $event
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
