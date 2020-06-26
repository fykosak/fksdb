<?php

namespace FKSDB\Modules\CoreModule;

use FKSDB\Modules\Core\AuthenticatedPresenter;
use FKSDB\Components\Grids\Payment\MyPaymentGrid;
use FKSDB\UI\PageTitle;

/**
 * Class MyPaymentsPresenter
 * @author Michal Červeňák <miso@fykos.cz>
 */
class MyPaymentsPresenter extends AuthenticatedPresenter {

    public function titleDefault() {
        $this->setPageTitle(new PageTitle(_('My payments'), 'fa fa-credit-card'));
    }

    protected function createComponentMyPaymentGrid(): MyPaymentGrid {
        return new MyPaymentGrid($this->getContext());
    }
}
