<?php

use FKSDB\Components\Grids\Payment\MyPaymentGrid;

/**
 * Class MyPaymentsPresenter
 * @author Michal Červeňák <miso@fykos.cz>
 */
class MyPaymentsPresenter extends AuthenticatedPresenter {

    public function titleDefault(): void {
        $this->setTitle(_('My payments'), 'fa fa-credit-card');
    }

    public function createComponentMyPaymentGrid(): MyPaymentGrid {
        return new MyPaymentGrid($this->getContext());
    }
}
