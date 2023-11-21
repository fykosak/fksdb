<?php

declare(strict_types=1);

namespace FKSDB\Components\DataTest\Tests\Event;

use FKSDB\Components\DataTest\Tests\Test;
use FKSDB\Models\Authorization\EventRole\FyziklaniTeamMemberRole;
use FKSDB\Models\Authorization\EventRole\FyziklaniTeamTeacherRole;
use FKSDB\Models\Authorization\EventRole\ParticipantRole;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Services\PersonService;
use Fykosak\NetteORM\Model\Model;
use Fykosak\Utils\Logging\Logger;
use Fykosak\Utils\Logging\Message;
use Fykosak\Utils\UI\Title;

/**
 * @phpstan-extends Test<EventModel>
 */
class NoRoleSchedule extends Test
{
    private PersonService $personService;

    public function inject(PersonService $personService): void
    {
        $this->personService = $personService;
    }

    /**
     * @param EventModel $model
     */
    public function run(Logger $logger, Model $model): void
    {
        $query = $this->personService->getTable()
            ->where(':person_schedule.schedule_item.schedule_group.event_id', $model->event_id)
            ->group('person_id');
        /** @var PersonModel $person */
        foreach ($query as $person) {
            foreach ($person->getEventRoles($model) as $role) {
                if (
                    $role instanceof FyziklaniTeamMemberRole
                    || $role instanceof FyziklaniTeamTeacherRole
                    || $role instanceof ParticipantRole
                ) {
                    continue 2;
                }
            }
            $logger->log(
                new Message(
                    sprintf(
                        _('Detect person "%s"(%d) on schedule without role.'),
                        $person->getFullName(),
                        $person->person_id
                    ),
                    Message::LVL_ERROR
                )
            );
        }
    }

    public function getTitle(): Title
    {
        return new Title(null, _('No role schedule'), 'fas fa-poo');
    }

    public function getId(): string
    {
        return 'eventScheduleNoRole';
    }
}
