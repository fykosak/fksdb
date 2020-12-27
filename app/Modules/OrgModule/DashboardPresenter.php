<?php

namespace FKSDB\Modules\OrgModule;

use FKSDB\Models\ORM\Models\ModelLogin;
use FKSDB\Models\UI\PageTitle;

/**
 * Homepage presenter.
 */
class DashboardPresenter extends BasePresenter {

    public function authorizedDefault(): void {
        /** @var ModelLogin $login */
        $login = $this->getUser()->getIdentity();
        $access = $login ? $login->isOrg($this->yearCalculator) : false;
        $this->setAuthorized($access);
    }

    public function titleDefault(): void {
        $this->setPageTitle(new PageTitle(_('Organiser\'s dashboard'), 'fa fa-dashboard'));
    }
}
