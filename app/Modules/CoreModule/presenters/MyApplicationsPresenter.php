<?php

namespace FKSDB\Modules\CoreModule;

use FKSDB\Components\Grids\PersonRelatedGrid;
use FKSDB\DBReflection\FieldLevelPermission;
use FKSDB\ORM\Models\ModelPerson;
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
        $this->setAuthorized($this->getUser()->isLoggedIn() && $this->getPerson());
    }

    public function titleDefault(): void {
        $this->setPageTitle(new PageTitle(_('My applications'), 'fa fa-calendar'));
    }

    protected function createComponentMySingleApplicationsGrid(): SingleApplicationsGrid {
        return new SingleApplicationsGrid($this->getPerson(), $this->getContext());
    }

    protected function createComponentMyTeamApplicationsGrid(): TeamApplicationsGrid {
        return new TeamApplicationsGrid($this->getPerson(), $this->getContext());
    }

    protected function createComponentNewApplicationsGrid(): NewApplicationsGrid {
        return new NewApplicationsGrid($this->getContext());
    }

    protected function createComponentMyEventTeachersGrid(): PersonRelatedGrid {
        return new PersonRelatedGrid('event_teacher', $this->getPerson(), FieldLevelPermission::ALLOW_FULL, $this->getContext());
    }

    private function getPerson(): ?ModelPerson {
        return $this->getUser()->getIdentity()->getPerson();
    }
}
