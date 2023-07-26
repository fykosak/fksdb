<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\Stalking;

use FKSDB\Components\Controls\Person\Detail\AddressComponent;
use FKSDB\Components\Controls\Person\Detail\Component;
use FKSDB\Components\Controls\Person\Detail\ContestantListComponent;
use FKSDB\Components\Controls\Person\Detail\FlagComponent;
use FKSDB\Components\Controls\Person\Detail\OrgListComponent;
use FKSDB\Components\Controls\Person\Detail\RoleComponent;
use FKSDB\Components\Controls\Person\Detail\ValidationComponent;
use FKSDB\Components\Controls\Stalking\Timeline\TimelineComponent;
use FKSDB\Components\Grids\PersonRelatedGrid;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Models\PostContactType;
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
        $this->template->userPermission = $this->userPermission;
        /** @phpstan-ignore-next-line */
        $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'container.latte');
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

    protected function createComponentContestantBasesGrid(): ContestantListComponent
    {
        return new ContestantListComponent($this->container, $this->person, $this->userPermission, true);
    }

    protected function createComponentTaskContributionsGrid(): PersonRelatedGrid
    {
        return new PersonRelatedGrid('task_contribution', $this->person, $this->userPermission, $this->getContext());
    }

    protected function createComponentOrgList(): OrgListComponent
    {
        return new OrgListComponent($this->container, $this->person, $this->userPermission, true);
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

    protected function createComponentStalkingComponent(): Component
    {
        return new Component(
            $this->getContext(),
            $this->person,
            $this->userPermission
        );
    }

    protected function createComponentPermanentAddress(): AddressComponent
    {
        return new AddressComponent(
            $this->getContext(),
            $this->person,
            $this->userPermission,
            PostContactType::tryFrom(PostContactType::PERMANENT)
        );
    }

    protected function createComponentDeliveryAddress(): AddressComponent
    {
        return new AddressComponent(
            $this->getContext(),
            $this->person,
            $this->userPermission,
            PostContactType::tryFrom(PostContactType::DELIVERY)
        );
    }

    protected function createComponentRole(): RoleComponent
    {
        return new RoleComponent($this->getContext(), $this->person, $this->userPermission);
    }

    protected function createComponentFlag(): FlagComponent
    {
        return new FlagComponent($this->getContext(), $this->person, $this->userPermission);
    }

    protected function createComponentValidation(): ValidationComponent
    {
        return new ValidationComponent($this->getContext(), $this->person, $this->userPermission);
    }

    protected function createComponentTimeline(): TimelineComponent
    {
        return new TimelineComponent($this->getContext(), $this->person);
    }
}
