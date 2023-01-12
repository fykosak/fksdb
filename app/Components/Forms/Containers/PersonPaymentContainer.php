<?php

declare(strict_types=1);

namespace FKSDB\Components\Forms\Containers;

use FKSDB\Components\Forms\Containers\Models\ContainerWithOptions;
use FKSDB\Models\Authorization\EventRole\FyziklaniTeamMemberRole;
use FKSDB\Models\Authorization\EventRole\FyziklaniTeamTeacherRole;
use FKSDB\Models\Exceptions\NotImplementedException;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use FKSDB\Models\ORM\Models\LoginModel;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Models\Schedule\PersonScheduleModel;
use FKSDB\Models\ORM\Services\Schedule\PersonScheduleService;
use FKSDB\Models\Transitions\Machine\PaymentMachine;
use Nette\DI\Container;
use Nette\Security\User;

class PersonPaymentContainer extends ContainerWithOptions
{
    private PersonScheduleService $personScheduleService;
    private PaymentMachine $machine;
    private bool $showAll;
    private User $user;
    private bool $isOrg;

    /**
     * @throws NotImplementedException
     */
    public function __construct(
        Container $container,
        PaymentMachine $machine,
        User $user,
        bool $isOrg,
        bool $showAll = true
    ) {
        parent::__construct($container);
        $this->user = $user;
        $this->machine = $machine;
        $this->showAll = $showAll;
        $this->isOrg = $isOrg;
        $this->configure();
    }

    final public function injectServicePersonSchedule(PersonScheduleService $personScheduleService): void
    {
        $this->personScheduleService = $personScheduleService;
    }

    /**
     * @throws NotImplementedException
     * @throws \Exception
     */
    protected function configure(): void
    {
        $query = $this->personScheduleService->getTable()
            ->where('schedule_item.schedule_group.event_id', $this->machine->event->event_id);
        if (!$this->isOrg) {
            /** @var LoginModel $login */
            $login = $this->user->getIdentity();
            $roles = $login->person->getEventRoles($this->machine->event);
            $teams = [];
            foreach ($roles as $role) {
                if ($role instanceof FyziklaniTeamTeacherRole) {
                    $teams += $role->teams;
                }
                if ($role instanceof FyziklaniTeamMemberRole) {
                    $teams[] = $role->member->fyziklani_team;
                }
            }
            $persons = [];
            /** @var TeamModel2 $team */
            foreach ($teams as $team) {
                $persons += $team->getPersons();
            }
            $query->where('person.person_id', array_map(fn(PersonModel $person): int => $person->person_id, $persons));
        }
        $query->order('person.family_name ,person_id');
        $lastPersonId = null;
        $container = null;
        /** @var PersonScheduleModel $model */
        foreach ($query as $model) {
            if (
                !$model->schedule_item->isPayable() ||
                in_array($model->schedule_item->schedule_group->schedule_group_type, $this->machine->scheduleGroupTypes)
            ) {
                continue;
            }
            if ($this->showAll || !$model->hasActivePayment()) {
                if ($model->person_id !== $lastPersonId) {
                    $container = new ModelContainer();
                    $this->addComponent($container, 'person' . $model->person_id);
                    $container->setOption('label', $model->person->getFullName());
                    $lastPersonId = $model->person_id;
                }
                $container->addCheckbox(
                    (string)$model->person_schedule_id,
                    $model->getLabel()
                    . ' ('
                    . $model->schedule_item->getPrice()->__toString()
                    . ')'
                );
            }
        }
    }
}
