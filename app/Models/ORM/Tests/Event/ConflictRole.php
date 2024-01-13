<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Tests\Event;

use FKSDB\Components\DataTest\TestLogger;
use FKSDB\Components\DataTest\TestMessage;
use FKSDB\Models\Authorization\EventRole\ContestOrganizerRole;
use FKSDB\Models\Authorization\EventRole\EventOrganizerRole;
use FKSDB\Models\Authorization\EventRole\EventRole;
use FKSDB\Models\Authorization\EventRole\Fyziklani\TeamMemberRole;
use FKSDB\Models\Authorization\EventRole\Fyziklani\TeamTeacherRole;
use FKSDB\Models\Authorization\EventRole\ParticipantRole;
use FKSDB\Models\ORM\Models\EventOrganizerModel;
use FKSDB\Models\ORM\Models\EventParticipantModel;
use FKSDB\Models\ORM\Models\Fyziklani\TeamMemberModel;
use FKSDB\Models\ORM\Models\Fyziklani\TeamTeacherModel;
use FKSDB\Models\ORM\Tests\Test;
use Fykosak\NetteORM\Model\Model;
use Fykosak\Utils\Logging\Message;
use Fykosak\Utils\UI\Title;

/**
 * @phpstan-extends Test<TeamMemberModel|TeamTeacherModel|EventParticipantModel|EventOrganizerModel>
 */
final class ConflictRole extends Test
{
    /**
     * @param TeamMemberModel|TeamTeacherModel|EventParticipantModel|EventOrganizerModel $model
     */
    protected function innerRun(TestLogger $logger, Model $model, string $id): void
    {
        $person = $model->person;
        if ($model instanceof TeamMemberModel || $model instanceof TeamTeacherModel) {
            $event = $model->fyziklani_team->event;
        } else {
            $event = $model->event;
        }
        $roles = $person->getEventRoles($event);
        $organizerRole = false;
        $participantRole = false;
        $teacherRole = false;
        foreach ($roles as $role) {
            if (
                $role instanceof TeamMemberRole
                || $role instanceof ParticipantRole
            ) {
                $participantRole = true;
            } elseif ($role instanceof TeamTeacherRole) {
                $teacherRole = true;
            } else{
                $organizerRole = true;
            }
        }
        if (((int)$participantRole + (int)$teacherRole + (int)$organizerRole) > 1) {
            $logger->log(
                new TestMessage(
                    $id,
                    sprintf(
                        _('Has conflict role %s.'),
                        join(', ', array_map(fn(EventRole $role) => $role->getRoleId(), $roles))
                    ),
                    Message::LVL_ERROR
                )
            );
        }
    }

    public function getTitle(): Title
    {
        return new Title(null, _('Conflict role'));
    }

    public function getId(): string
    {
        return 'conflictRole';
    }
}
