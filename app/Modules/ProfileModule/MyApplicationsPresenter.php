<?php

declare(strict_types=1);

namespace FKSDB\Modules\ProfileModule;

use FKSDB\Components\Grids\Application\Person\NewApplicationsGrid;
use FKSDB\Components\Grids\PersonRelatedGrid;
use FKSDB\Models\ORM\FieldLevelPermission;
use Fykosak\Utils\UI\PageTitle;

class MyApplicationsPresenter extends BasePresenter
{
    public function titleDefault(): PageTitle
    {
        return new PageTitle(null, _('My applications'), 'fas fa-calendar-alt');
    }

    public function authorizedDefault(): bool
    {
        return true;
    }

    protected function createComponentNewApplicationsGrid(): NewApplicationsGrid
    {
        return new NewApplicationsGrid($this->getContext());
    }

    protected function createComponentEventTeachersGrid(): PersonRelatedGrid
    {
        return new PersonRelatedGrid(
            'fyziklani_team_teacher',
            $this->getLoggedPerson(),
            FieldLevelPermission::ALLOW_FULL,
            $this->getContext()
        );
    }

    protected function createComponentEventParticipantsGrid(): PersonRelatedGrid
    {
        return new PersonRelatedGrid(
            'event_participant',
            $this->getLoggedPerson(),
            FieldLevelPermission::ALLOW_FULL,
            $this->getContext()
        );
    }

    protected function createComponentTeamMembersGrid(): PersonRelatedGrid
    {
        return new PersonRelatedGrid(
            'fyziklani_team_member',
            $this->getLoggedPerson(),
            FieldLevelPermission::ALLOW_FULL,
            $this->getContext()
        );
    }
}
