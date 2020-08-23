<?php

namespace FKSDB\Modules\CoreModule;

use FKSDB\Components\Grids\Events\Application\MySingleApplicationsGrid;
use FKSDB\Components\Grids\Events\Application\MyTeamApplicationsGrid;
use FKSDB\Components\Grids\Events\Application\NewApplicationsGrid;
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

    protected function createComponentMySingleApplicationsGrid(): MySingleApplicationsGrid {
        return new MySingleApplicationsGrid($this->getUser()->getIdentity()->getPerson(), $this->getContext());
    }

    protected function createComponentMyTeamApplicationsGrid(): MyTeamApplicationsGrid {
        return new MyTeamApplicationsGrid($this->getUser()->getIdentity()->getPerson(), $this->getContext());
    }

    protected function createComponentNewApplicationsGrid(): NewApplicationsGrid {
        return new NewApplicationsGrid($this->getContext());
    }
}
