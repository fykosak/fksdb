<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\Person\Detail;

use FKSDB\Components\Controls\Person\Detail\Timeline\TimelineComponent;
use FKSDB\Components\Grids\PersonRelatedGrid;
use FKSDB\Models\ORM\FieldLevelPermissionValue;
use FKSDB\Models\ORM\Models\PersonModel;
use Fykosak\Utils\BaseComponent\BaseComponent;
use Nette\DI\Container;

class ContainerComponent extends BaseComponent
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
        $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'container.latte');
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

    protected function createComponentFyziklaniTeacherList(): FyziklaniTeamTeacherListComponent
    {
        return new FyziklaniTeamTeacherListComponent(
            $this->getContext(),
            $this->person,
            $this->userPermission,
            false
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

    protected function createComponentDetailComponent(): Component
    {
        return new Component($this->getContext(), $this->person, $this->userPermission, true);
    }

    protected function createComponentAddresses(): AddressComponent
    {
        return new AddressComponent($this->getContext(), $this->person, $this->userPermission, true);
    }

    protected function createComponentPaymentList(): PaymentListComponent
    {
        return new PaymentListComponent($this->getContext(), $this->person, $this->userPermission, true);
    }

    protected function createComponentContestantList(): ContestantListComponent
    {
        return new ContestantListComponent($this->getContext(), $this->person, $this->userPermission, true);
    }

    protected function createComponentOrgList(): OrgListComponent
    {
        return new OrgListComponent($this->getContext(), $this->person, $this->userPermission, true);
    }

    protected function createComponentHistoryList(): HistoryListComponent
    {
        return new HistoryListComponent($this->getContext(), $this->person, $this->userPermission, true);
    }

    protected function createComponentTaskContributionList(): TaskContributionListComponent
    {
        return new TaskContributionListComponent($this->getContext(), $this->person, $this->userPermission, true);
    }

    protected function createComponentRole(): RoleComponent
    {
        return new RoleComponent($this->getContext(), $this->person, $this->userPermission, true);
    }

    protected function createComponentFlag(): FlagComponent
    {
        return new FlagComponent($this->getContext(), $this->person, $this->userPermission, true);
    }

    protected function createComponentValidation(): ValidationComponent
    {
        return new ValidationComponent($this->getContext(), $this->person, $this->userPermission, true);
    }

    protected function createComponentTimeline(): TimelineComponent
    {
        return new TimelineComponent($this->getContext(), $this->person);
    }
}
