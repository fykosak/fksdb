<?php

namespace FKSDB\Modules\CoreModule;

use FKSDB\Components\Grids\PersonRelatedGrid;
use FKSDB\Models\ORM\FieldLevelPermission;
use FKSDB\Models\ORM\Models\ModelPerson;
use FKSDB\Components\Grids\Application\Person\NewApplicationsGrid;
use FKSDB\Models\UI\PageTitle;

class MyApplicationsPresenter extends BasePresenter {

    public function authorizedDefault(): void {
        $this->setAuthorized($this->getUser()->isLoggedIn() && $this->getPerson());
    }

    public function titleDefault(): void {
        $this->setPageTitle(new PageTitle(_('My applications'), 'fa fa-calendar-alt'));
    }

    protected function createComponentNewApplicationsGrid(): NewApplicationsGrid {
        return new NewApplicationsGrid($this->getContext());
    }

    protected function createComponentMyEventTeachersGrid(): PersonRelatedGrid {
        return new PersonRelatedGrid('event_teacher', $this->getPerson(), FieldLevelPermission::ALLOW_FULL, $this->getContext());
    }

    protected function createComponentMyApplicationsGrid(): PersonRelatedGrid {
        return new PersonRelatedGrid('event_participant', $this->getPerson(), FieldLevelPermission::ALLOW_FULL, $this->getContext());
    }

    private function getPerson(): ?ModelPerson {
        return $this->getUser()->getIdentity()->getPerson();
    }
}
