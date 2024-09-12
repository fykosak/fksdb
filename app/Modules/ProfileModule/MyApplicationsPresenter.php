<?php

declare(strict_types=1);

namespace FKSDB\Modules\ProfileModule;

use FKSDB\Components\Applications\NewApplicationsGrid;
use FKSDB\Components\Grids\PersonRelatedGrid;
use FKSDB\Models\ORM\FieldLevelPermission;
use FKSDB\Models\ORM\Models\EventParticipantModel;
use FKSDB\Models\ORM\Models\Fyziklani\TeamMemberModel;
use FKSDB\Models\ORM\Models\Fyziklani\TeamTeacherModel;
use Fykosak\Utils\UI\PageTitle;

final class MyApplicationsPresenter extends BasePresenter
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

    /**
     * @phpstan-return PersonRelatedGrid<TeamTeacherModel>
     */
    protected function createComponentEventTeachersGrid(): PersonRelatedGrid
    {
        /** @phpstan-ignore-next-line */
        return new PersonRelatedGrid(
            'fyziklani_team_teacher',
            $this->getLoggedPerson(),
            FieldLevelPermission::ALLOW_FULL,
            $this->getContext()
        );
    }

    /**
     * @phpstan-return PersonRelatedGrid<EventParticipantModel>
     */
    protected function createComponentEventParticipantsGrid(): PersonRelatedGrid
    {
        /** @phpstan-ignore-next-line */
        return new PersonRelatedGrid(
            'event_participant',
            $this->getLoggedPerson(),
            FieldLevelPermission::ALLOW_FULL,
            $this->getContext()
        );
    }

    /**
     * @phpstan-return PersonRelatedGrid<TeamMemberModel>
     */
    protected function createComponentTeamMembersGrid(): PersonRelatedGrid
    {
        /** @phpstan-ignore-next-line */
        return new PersonRelatedGrid(
            'fyziklani_team_member',
            $this->getLoggedPerson(),
            FieldLevelPermission::ALLOW_FULL,
            $this->getContext()
        );
    }
}
