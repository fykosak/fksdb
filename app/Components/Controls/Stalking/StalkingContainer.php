<?php

namespace FKSDB\Components\Controls\Stalking;

use FKSDB\Components\Controls\BaseComponent;
use FKSDB\Components\Controls\Stalking\StalkingComponent\StalkingComponent;
use FKSDB\Components\Controls\Stalking\Timeline\TimelineControl;
use FKSDB\Components\Grids\PersonRelatedGrid;
use FKSDB\Model\ORM\Models\ModelPerson;
use Nette\DI\Container;
use FKSDB\Components\Controls\Stalking\Components;

/**
 * Class StalkingContainer
 * @author Michal Červeňák <miso@fykos.cz>
 */
class StalkingContainer extends BaseComponent {

    private ModelPerson $person;

    private int $userPermission;

    public function __construct(Container $container, ModelPerson $person, int $userPermission) {
        parent::__construct($container);
        $this->person = $person;
        $this->userPermission = $userPermission;
    }

    public function render(): void {
        $this->template->userPermissions = $this->userPermission;
        $this->template->person = $this->person;
        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'layout.container.latte');
        $this->template->render();
    }

    protected function createComponentPersonHistoryGrid(): PersonRelatedGrid {
        return new PersonRelatedGrid('person_history', $this->person, $this->userPermission, $this->getContext());
    }

    protected function createComponentEventOrgsGrid(): PersonRelatedGrid {
        return new PersonRelatedGrid('event_org', $this->person, $this->userPermission, $this->getContext());
    }

    protected function createComponentPaymentsGrid(): PersonRelatedGrid {
        return new PersonRelatedGrid('payment', $this->person, $this->userPermission, $this->getContext());
    }

    protected function createComponentContestantBasesGrid(): PersonRelatedGrid {
        return new PersonRelatedGrid('contestant_base', $this->person, $this->userPermission, $this->getContext());
    }

    protected function createComponentTaskContributionsGrid(): PersonRelatedGrid {
        return new PersonRelatedGrid('task_contribution', $this->person, $this->userPermission, $this->getContext());
    }

    protected function createComponentEventTeachersGrid(): PersonRelatedGrid {
        return new PersonRelatedGrid('event_teacher', $this->person, $this->userPermission, $this->getContext());
    }

    protected function createComponentEventParticipantsGrid(): PersonRelatedGrid {
        return new PersonRelatedGrid('event_participant', $this->person, $this->userPermission, $this->getContext());
    }

    protected function createComponentStalkingComponent(): StalkingComponent {
        return new StalkingComponent($this->getContext());
    }

    protected function createComponentAddress(): Components\Address {
        return new Components\Address($this->getContext());
    }

    protected function createComponentRole(): Components\Role {
        return new Components\Role($this->getContext());
    }

    protected function createComponentFlag(): Components\Flag {
        return new Components\Flag($this->getContext());
    }

    protected function createComponentSchedule(): Components\Schedule {
        return new Components\Schedule($this->getContext());
    }

    protected function createComponentValidation(): Components\Validation {
        return new Components\Validation($this->getContext());
    }

    protected function createComponentTimeline(): TimelineControl {
        return new TimelineControl($this->getContext(), $this->person);
    }
}
