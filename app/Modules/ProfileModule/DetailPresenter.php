<?php

declare(strict_types=1);

namespace FKSDB\Modules\ProfileModule;

use FKSDB\Components\Controls\Person\Detail\ContestantListComponent;
use FKSDB\Components\Controls\Person\Detail\FyziklaniTeamTeacherListComponent;
use FKSDB\Components\Controls\Person\Detail\HistoryListComponent;
use FKSDB\Components\Controls\Person\Detail\OrgListComponent;
use FKSDB\Components\Controls\Person\Detail\PaymentListComponent;
use FKSDB\Components\Controls\Person\Detail\RoleComponent;
use FKSDB\Components\Grids\Application\Person\NewApplicationsGrid;
use FKSDB\Components\Grids\PersonRelatedGrid;
use FKSDB\Models\ORM\FieldLevelPermissionValue;
use FKSDB\Modules\CoreModule\BasePresenter;
use Fykosak\Utils\UI\PageTitle;

class DetailPresenter extends BasePresenter
{
    public function titleDefault(): PageTitle
    {
        return new PageTitle(null, _('My profile'), 'fa fa-cogs');
    }

    public function titleApplications(): PageTitle
    {
        return new PageTitle(null, _('My applications'), 'fa fa-calendar-alt');
    }

    public function renderDefault(): void
    {
        $this->template->login = $this->getUser()->getIdentity();
        $this->template->person = $this->getLoggedPerson();
    }

    protected function createComponentPaymentList(): PaymentListComponent
    {
        return new PaymentListComponent(
            $this->getContext(),
            $this->getLoggedPerson(),
            FieldLevelPermissionValue::Full,
            false,
        );
    }

    protected function createComponentOrgList(): OrgListComponent
    {
        return new OrgListComponent(
            $this->getContext(),
            $this->getLoggedPerson(),
            FieldLevelPermissionValue::Full,
            false,
        );
    }

    protected function createComponentFyziklaniTeacherList(): FyziklaniTeamTeacherListComponent
    {
        return new FyziklaniTeamTeacherListComponent(
            $this->getContext(),
            $this->getLoggedPerson(),
            FieldLevelPermissionValue::Full,
            false,
        );
    }

    protected function createComponentRole(): RoleComponent
    {
        return new RoleComponent(
            $this->getContext(),
            $this->getLoggedPerson(),
            FieldLevelPermissionValue::Full,
            false,
        );
    }

    protected function createComponentContestantList(): ContestantListComponent
    {
        return new ContestantListComponent(
            $this->getContext(),
            $this->getLoggedPerson(),
            FieldLevelPermissionValue::Full,
            false
        );
    }

    protected function createComponentHistoryList(): HistoryListComponent
    {
        return new HistoryListComponent(
            $this->getContext(),
            $this->getLoggedPerson(),
            FieldLevelPermissionValue::Full,
            false
        );
    }


    protected function createComponentMyPaymentGrid(): PaymentListComponent
    {
        return new PaymentListComponent(
            $this->getContext(),
            $this->getLoggedPerson(),
            FieldLevelPermissionValue::Full,
            false
        );
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
            FieldLevelPermissionValue::Full,
            $this->getContext()
        );
    }

    protected function createComponentEventParticipantsGrid(): PersonRelatedGrid
    {
        return new PersonRelatedGrid(
            'event_participant',
            $this->getLoggedPerson(),
            FieldLevelPermissionValue::Full,
            $this->getContext()
        );
    }

    protected function createComponentTeamMembersGrid(): PersonRelatedGrid
    {
        return new PersonRelatedGrid(
            'fyziklani_team_member',
            $this->getLoggedPerson(),
            FieldLevelPermissionValue::Full,
            $this->getContext()
        );
    }
}
