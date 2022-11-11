<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\Stalking;

use FKSDB\Components\Controls\Stalking\Components;
use FKSDB\Components\Controls\Stalking\StalkingComponent\StalkingComponent;
use FKSDB\Components\Controls\Stalking\Timeline\TimelineComponent;
use FKSDB\Components\Grids\PersonRelatedGrid;
use FKSDB\Models\ORM\FieldLevelPermissionValue;
use FKSDB\Models\ORM\Models\PersonModel;
use Fykosak\Utils\BaseComponent\BaseComponent;
use Nette\DI\Container;

class StalkingContainer extends BaseComponent
{

    private PersonModel $person;
    private FieldLevelPermissionValue $userPermission;

    public function __construct(Container $container, PersonModel $person, FieldLevelPermissionValue $userPermission)
    {
        parent::__construct($container);
        $this->person = $person;
        $this->userPermission = $userPermission;
    }

    final public function render(): void
    {
        $this->template->userPermission = $this->userPermission;
        $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'layout.container.latte');
    }

    protected function createComponentPersonHistoryGrid(): PersonRelatedGrid
    {
        return new PersonRelatedGrid('person_history', $this->person, $this->userPermission, $this->getContext());
    }

    protected function createComponentEventOrgsGrid(): PersonRelatedGrid
    {
        return new PersonRelatedGrid('event_org', $this->person, $this->userPermission, $this->getContext());
    }

    protected function createComponentContestantBasesGrid(): PersonRelatedGrid
    {
        return new PersonRelatedGrid('contestant', $this->person, $this->userPermission, $this->getContext());
    }

    protected function createComponentEventTeachersGrid(): PersonRelatedGrid
    {
        return new PersonRelatedGrid(
            'fyziklani_team_teacher',
            $this->person,
            $this->userPermission,
            $this->getContext()
        );
    }

    protected function createComponentEventParticipantsGrid(): PersonRelatedGrid
    {
        return new PersonRelatedGrid('event_participant', $this->person, $this->userPermission, $this->getContext());
    }

    protected function createComponentTeamMembersGrid(): PersonRelatedGrid
    {
        return new PersonRelatedGrid(
            'fyziklani_team_member',
            $this->person,
            $this->userPermission,
            $this->getContext()
        );
    }

    protected function createComponentEventScheduleGrid(): PersonRelatedGrid
    {
        return new PersonRelatedGrid('schedule_item', $this->person, $this->userPermission, $this->getContext());
    }

    protected function createComponentEmailMessageGrid(): PersonRelatedGrid
    {
        return new PersonRelatedGrid('email_message', $this->person, $this->userPermission, $this->getContext());
    }

    protected function createComponentStalkingComponent(): StalkingComponent
    {
        return new StalkingComponent($this->getContext(), $this->person, $this->userPermission);
    }

    protected function createComponentAddresses(): Components\AddressComponent
    {
        return new Components\AddressComponent(
            $this->getContext(),
            $this->person,
            $this->userPermission
        );
    }

    protected function createComponentPaymentList(): Components\PaymentListComponent
    {
        return new Components\PaymentListComponent(
            $this->getContext(),
            $this->person,
            $this->userPermission
        );
    }

    protected function createComponentContestantList(): Components\ContestantListComponent
    {
        return new Components\ContestantListComponent(
            $this->getContext(),
            $this->person,
            $this->userPermission,
            true
        );
    }

    protected function createComponentOrgList(): Components\OrgListComponent
    {
        return new Components\OrgListComponent(
            $this->getContext(),
            $this->person,
            $this->userPermission,
            true
        );
    }

    protected function createComponentHistoryList(): Components\HistoryListComponent
    {
        return new Components\HistoryListComponent(
            $this->getContext(),
            $this->person,
            $this->userPermission,
            true
        );
    }

    protected function createComponentTaskContributionList(): Components\TaskContributionListComponent
    {
        return new Components\TaskContributionListComponent(
            $this->getContext(),
            $this->person,
            $this->userPermission,
            true
        );
    }

    protected function createComponentRole(): Components\RoleComponent
    {
        return new Components\RoleComponent($this->getContext(), $this->person, $this->userPermission);
    }

    protected function createComponentFlag(): Components\FlagComponent
    {
        return new Components\FlagComponent($this->getContext(), $this->person, $this->userPermission);
    }

    protected function createComponentValidation(): Components\ValidationComponent
    {
        return new Components\ValidationComponent($this->getContext(), $this->person, $this->userPermission);
    }

    protected function createComponentTimeline(): TimelineComponent
    {
        return new TimelineComponent($this->getContext(), $this->person);
    }
}
