<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\Stalking;

use FKSDB\Components\Controls\Stalking\Components;
use FKSDB\Components\Controls\Stalking\StalkingComponent\StalkingComponent;
use FKSDB\Components\Controls\Stalking\Timeline\TimelineComponent;
use FKSDB\Components\Grids\PersonRelatedGrid;
use FKSDB\Models\ORM\Models\PersonModel;
use Fykosak\Utils\BaseComponent\BaseComponent;
use Nette\DI\Container;

class StalkingContainer extends BaseComponent
{

    private PersonModel $person;
    private int $userPermission;

    public function __construct(Container $container, PersonModel $person, int $userPermission)
    {
        parent::__construct($container);
        $this->person = $person;
        $this->userPermission = $userPermission;
    }

    final public function render(): void
    {
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

    protected function createComponentPaymentsGrid(): PersonRelatedGrid
    {
        return new PersonRelatedGrid('payment', $this->person, $this->userPermission, $this->getContext());
    }

    protected function createComponentContestantBasesGrid(): PersonRelatedGrid
    {
        return new PersonRelatedGrid('contestant', $this->person, $this->userPermission, $this->getContext());
    }

    protected function createComponentTaskContributionsGrid(): PersonRelatedGrid
    {
        return new PersonRelatedGrid('task_contribution', $this->person, $this->userPermission, $this->getContext());
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

    protected function createComponentAddress(): Components\AddressComponent
    {
        return new Components\AddressComponent($this->getContext(), $this->person, $this->userPermission);
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
