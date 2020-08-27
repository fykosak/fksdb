<?php

namespace FKSDB\Modules\CoreModule;

use FKSDB\Components\Grids\Payment\PersonPaymentsGrid;
use FKSDB\UI\PageTitle;

/**
 * Class MyPaymentsPresenter
 * @author Michal Červeňák <miso@fykos.cz>
 */
class MyPaymentsPresenter extends BasePresenter {

    public function authorizedDefault(): void {
        $this->setAuthorized($this->getUser()->isLoggedIn() && $this->getUser()->getIdentity()->getPerson());
    }

    public function titleDefault(): void {
        $this->setPageTitle(new PageTitle(_('My payments'), 'fa fa-credit-card'));
    }

    protected function createComponentMyPaymentGrid(): PersonPaymentsGrid {
        return new PersonPaymentsGrid($this->getContext(), $this->getUser()->getIdentity()->getPerson());
    }
}
