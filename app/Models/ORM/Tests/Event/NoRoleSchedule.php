<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Tests\Event;

use FKSDB\Components\DataTest\TestLogger;
use FKSDB\Components\DataTest\TestMessage;
use FKSDB\Models\Authorization\EventRole\FyziklaniTeamMemberRole;
use FKSDB\Models\Authorization\EventRole\FyziklaniTeamTeacherRole;
use FKSDB\Models\Authorization\EventRole\ParticipantRole;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Services\PersonService;
use FKSDB\Models\ORM\Tests\Test;
use Fykosak\NetteORM\Model\Model;
use Fykosak\Utils\Logging\Message;
use Fykosak\Utils\UI\Title;

/**
 * @phpstan-extends Test<EventModel>
 */
final class NoRoleSchedule extends Test
{
    private PersonService $personService;

    public function inject(PersonService $personService): void
    {
        $this->personService = $personService;
    }

    /**
     * @param EventModel $model
     */
    public function run(TestLogger $logger, Model $model): void
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
                new TestMessage(
                    $this->formatId($model),
                    sprintf(
                        _('Detect person "%s"(%d) in schedule without any role.'),
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
        return new Title(null, _('People without role'), 'fas fa-poo');
    }

    public function getId(): string
    {
        return 'eventScheduleNoRole';
    }
}
