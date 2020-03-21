<?php

namespace FKSDB\Components\Factories;

use FKSDB\Components\Forms\Controls\Autocomplete\PersonProvider;
use FKSDB\Components\Forms\Controls\Payment\SelectForm;
use FKSDB\Components\Forms\Factories\PersonFactory;
use FKSDB\Components\Forms\Factories\TableReflectionFactory;
use FKSDB\Components\Grids\Payment\OrgPaymentGrid;
use FKSDB\ORM\Models\ModelEvent;
use FKSDB\ORM\Services\Schedule\ServicePersonSchedule;
use FKSDB\ORM\Services\Schedule\ServiceSchedulePayment;
use FKSDB\ORM\Services\ServicePayment;
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
     * @var ServicePersonSchedule
     */
    private $servicePersonSchedule;
    /**
     * @var ServiceSchedulePayment
     */
    private $serviceSchedulePayment;
    /**
     * @var TableReflectionFactory
     */
    private $tableReflectionFactory;

    /**
     * PaymentFactory constructor.
     * @param TableReflectionFactory $tableReflectionFactory
     * @param ServiceSchedulePayment $serviceSchedulePayment
     * @param PersonFactory $personFactory
     * @param PersonProvider $personProvider
     * @param ServicePersonSchedule $servicePersonSchedule
     * @param ITranslator $translator
     * @param ServicePayment $servicePayment
     */
    public function __construct(TableReflectionFactory $tableReflectionFactory,
                                ServiceSchedulePayment $serviceSchedulePayment,
                                PersonFactory $personFactory,
                                PersonProvider $personProvider,
                                ServicePersonSchedule $servicePersonSchedule,
                                ITranslator $translator, ServicePayment $servicePayment) {
        $this->tableReflectionFactory = $tableReflectionFactory;
        $this->translator = $translator;
        $this->servicePayment = $servicePayment;
        $this->personFactory = $personFactory;
        $this->personProvider = $personProvider;
        $this->servicePersonSchedule = $servicePersonSchedule;
        $this->serviceSchedulePayment = $serviceSchedulePayment;
    }

    /**
     * @param ModelEvent $event
     * @return OrgPaymentGrid
     */
    public function createOrgGrid(ModelEvent $event): OrgPaymentGrid {
        return new OrgPaymentGrid($this->servicePayment, $event, $this->tableReflectionFactory);
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
            'accommodation',
            $this->translator,
            $this->servicePayment,
            $machine,
            $this->personFactory,
            $this->personProvider,
            $this->servicePersonSchedule,
            $this->serviceSchedulePayment
        );
    }
}
