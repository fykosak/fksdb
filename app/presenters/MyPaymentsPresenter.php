<?php

use FKSDB\Components\Grids\Payment\MyPaymentGrid;

class MyPaymentsPresenter extends AuthenticatedPresenter {
    /**
     * @var \ServicePayment
     */
    private $servicePayment;

    public function injectServicePayment(\ServicePayment $servicePayment) {
        $this->servicePayment = $servicePayment;
    }

    public function titleDefault() {
        $this->setTitle(_('My payments'));
        $this->setIcon('fa fa-credit-card');
    }

    public function createComponentMyPaymentGrid(): MyPaymentGrid {
        return new MyPaymentGrid($this->servicePayment);
    }

}
