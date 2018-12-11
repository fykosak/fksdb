<?php

use FKSDB\Components\Grids\EventPayment\MyPaymentGrid;

class MyPaymentPresenter extends AuthenticatedPresenter {
    /**
     * @var \ServicePayment
     */
    private $serviceEventPayment;

    public function injectServiceEventPayment(\ServicePayment $serviceEventPayment) {
        $this->serviceEventPayment = $serviceEventPayment;
    }

    public function titleDefault() {
        $this->setTitle(_('My payment'));
        $this->setIcon('fa fa-credit-card');
    }

    public function createComponentMyPaymentGrid(): MyPaymentGrid {
        return new MyPaymentGrid($this->serviceEventPayment);
    }

}
