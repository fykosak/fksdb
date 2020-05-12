<?php

use FKSDB\Components\Grids\Payment\MyPaymentGrid;

/**
 * Class MyPaymentsPresenter
 */
class MyPaymentsPresenter extends AuthenticatedPresenter {

    public function titleDefault() {
        $this->setTitle(_('My payments'), 'fa fa-credit-card');
    }

    /**
     * @return MyPaymentGrid
     */
    public function createComponentMyPaymentGrid(): MyPaymentGrid {
        return new MyPaymentGrid($this->getContext());
    }
}
