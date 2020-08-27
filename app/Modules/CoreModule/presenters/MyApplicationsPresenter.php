<?php

namespace FKSDB\Modules\CoreModule;

use FKSDB\Components\Grids\Application\Person\{
    SingleApplicationsGrid,
    TeamApplicationsGrid,
    NewApplicationsGrid,
};
use FKSDB\UI\PageTitle;

/**
 * Class MyApplicationsPresenter
 * @author Michal Červeňák <miso@fykos.cz>
 */
class MyApplicationsPresenter extends BasePresenter {

    public function authorizedDefault(): void {
        $this->setAuthorized($this->getUser()->isLoggedIn() && $this->getUser()->getIdentity()->getPerson());
    }

    public function titleDefault(): void {
        $this->setPageTitle(new PageTitle(_('My applications'), 'fa fa-calendar'));
    }

    protected function createComponentMySingleApplicationsGrid(): SingleApplicationsGrid {
        return new SingleApplicationsGrid($this->getUser()->getIdentity()->getPerson(), $this->getContext());
    }

    protected function createComponentMyTeamApplicationsGrid(): TeamApplicationsGrid {
        return new TeamApplicationsGrid($this->getUser()->getIdentity()->getPerson(), $this->getContext());
    }

    protected function createComponentNewApplicationsGrid(): NewApplicationsGrid {
        return new NewApplicationsGrid($this->getContext());
    }
}
