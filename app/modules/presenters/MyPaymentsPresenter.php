<?php

use FKSDB\Components\Forms\Factories\TableReflectionFactory;
use FKSDB\Components\Grids\Payment\MyPaymentGrid;
use FKSDB\ORM\Services\ServicePayment;

/**
 * Class MyPaymentsPresenter
 */
class MyPaymentsPresenter extends AuthenticatedPresenter {
    /**
     * @var ServicePayment
     */
    private $servicePayment;
    /**
     * @var TableReflectionFactory
     */
    private $tableReflectionFactory;

    /**
     * @param ServicePayment $servicePayment
     */
    public function injectServicePayment(ServicePayment $servicePayment) {
        $this->servicePayment = $servicePayment;
    }

    /**
     * @param TableReflectionFactory $tableReflectionFactory
     */
    public function injectTableReflectionFactory(TableReflectionFactory $tableReflectionFactory) {
        $this->tableReflectionFactory = $tableReflectionFactory;
    }


    public function titleDefault() {
        $this->setTitle(_('My payments'));
        $this->setIcon('fa fa-credit-card');
    }

    /**
     * @return MyPaymentGrid
     */
    /**
     * @return MyPaymentGrid
     */
    public function createComponentMyPaymentGrid(): MyPaymentGrid {
        return new MyPaymentGrid($this->servicePayment, $this->tableReflectionFactory);
    }

}
