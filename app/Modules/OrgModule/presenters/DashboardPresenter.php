<?php

namespace FKSDB\Modules\OrgModule;

use FKSDB\ORM\Models\ModelLogin;
use FKSDB\UI\PageTitle;

/**
 * Homepage presenter.
 */
class DashboardPresenter extends BasePresenter {

    public function authorizedDefault() {
        /**
         * @var ModelLogin $login
         */
        $login = $this->getUser()->getIdentity();
        $access = $login ? $login->isOrg($this->getYearCalculator()) : false;
        $this->setAuthorized($access);
    }

    public function titleDefault() {
        $this->setPageTitle(new PageTitle(_('Organizátorský pultík'), 'fa fa-dashboard'));
    }
}
