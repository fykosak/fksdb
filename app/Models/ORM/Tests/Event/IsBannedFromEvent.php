<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Tests\Event;

use FKSDB\Components\DataTest\TestLogger;
use FKSDB\Components\DataTest\TestMessage;
use FKSDB\Models\ORM\Models\BannedPersonModel;
use FKSDB\Models\ORM\Models\EventOrganizerModel;
use FKSDB\Models\ORM\Models\EventParticipantModel;
use FKSDB\Models\ORM\Models\Fyziklani\TeamMemberModel;
use FKSDB\Models\ORM\Models\Fyziklani\TeamTeacherModel;
use FKSDB\Models\ORM\Tests\Test;
use Fykosak\NetteORM\Model\Model;
use Fykosak\Utils\Logging\Message;
use Fykosak\Utils\UI\Title;

/**
 * @phpstan-extends Test<EventParticipantModel|TeamMemberModel|TeamTeacherModel|EventOrganizerModel>
 */
final class IsBannedFromEvent extends Test
{
    /**
     * @param EventParticipantModel|TeamMemberModel|TeamTeacherModel|EventOrganizerModel $model
     */
    protected function innerRun(TestLogger $logger, Model $model, string $id): void
    {
        $person = $model->person;
        if ($model instanceof EventParticipantModel || $model instanceof EventOrganizerModel) {
            $event = $model->event;
        } else {
            $event = $model->fyziklani_team->event;
        }
        /**
         * @var BannedPersonModel $ban
         */
        foreach ($person->getBans() as $ban) {
            if ($ban->getBanForEvent($event)) {
                $logger->log(new TestMessage(
                    $id,
                    sprintf(_('Person was banned from event, caseId: %s'), $ban->case_id),
                    Message::LVL_ERROR
                ));
            }
        }
    }

    public function getTitle(): Title
    {
        return new Title(null, _('Detect participants, team members or team teachers banned from an event'));
    }

    public function getId(): string
    {
        return 'IsBannedEvent';
    }
}
