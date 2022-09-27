<?php

declare(strict_types=1);

namespace FKSDB\Modules\CoreModule;

use FKSDB\Components\Grids\Application\Person\NewApplicationsGrid;
use FKSDB\Components\Grids\PersonRelatedGrid;
use FKSDB\Models\ORM\FieldLevelPermission;
use FKSDB\Models\ORM\Models\PersonModel;
use Fykosak\Utils\UI\PageTitle;

class MyApplicationsPresenter extends BasePresenter
{

    public function authorizedDefault(): void
    {
        $this->setAuthorized($this->getUser()->isLoggedIn() && $this->getPerson());
    }

    private function getPerson(): ?PersonModel
    {
        return $this->getUser()->getIdentity()->person;
    }

    public function titleDefault(): PageTitle
    {
        return new PageTitle(null, _('My applications'), 'fa fa-calendar-alt');
    }

    protected function createComponentNewApplicationsGrid(): NewApplicationsGrid
    {
        return new NewApplicationsGrid($this->getContext());
    }

    protected function createComponentEventTeachersGrid(): PersonRelatedGrid
    {
        return new PersonRelatedGrid(
            'fyziklani_team_teacher',
            $this->getPerson(),
            FieldLevelPermission::ALLOW_FULL,
            $this->getContext()
        );
    }

    protected function createComponentEventParticipantsGrid(): PersonRelatedGrid
    {
        return new PersonRelatedGrid(
            'event_participant',
            $this->getPerson(),
            FieldLevelPermission::ALLOW_FULL,
            $this->getContext()
        );
    }

    protected function createComponentTeamMembersGrid(): PersonRelatedGrid
    {
        return new PersonRelatedGrid(
            'fyziklani_team_member',
            $this->getPerson(),
            FieldLevelPermission::ALLOW_FULL,
            $this->getContext()
        );
    }
}
