<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Tests\Event;

use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\EventOrganizerModel;
use FKSDB\Models\ORM\Models\EventParticipantModel;
use FKSDB\Models\ORM\Models\Fyziklani\TeamMemberModel;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use FKSDB\Models\ORM\Models\Fyziklani\TeamTeacherModel;
use FKSDB\Models\ORM\Tests\Adapter;
use Fykosak\NetteORM\Model\Model;

/**
 * @phpstan-extends Adapter<EventModel,TeamMemberModel|TeamTeacherModel|EventParticipantModel|EventOrganizerModel>
 */
class EventToPersonsAdapter extends Adapter
{
    /**
     * @param EventModel $model
     */
    protected function getModels(Model $model): iterable
    {
        $persons = [];
        /** @var EventParticipantModel $participant */
        foreach ($model->getParticipants() as $participant) {
            $persons[$participant->person_id] = $participant;
        }
        /** @var EventOrganizerModel $organizer */
        foreach ($model->getEventOrganizers() as $organizer) {
            $persons[$organizer->person_id] = $organizer;
        }

        /** @var TeamModel2 $team */
        foreach ($model->getTeams() as $team) {
            /** @var TeamMemberModel $member */
            foreach ($team->getMembers() as $member) {
                $persons[$member->person_id] = $member;
            }
            /** @var TeamTeacherModel $teacher */
            foreach ($team->getTeachers() as $teacher) {
                $persons[$teacher->person_id] = $teacher;
            }
        }
        return $persons;
    }

    protected function getLogPrepend(Model $model): string
    {
        return sprintf(_('In person %s: '), $model->person->getFullName());
    }

    public function getId(): string
    {
        return 'persons' . $this->test->getId();
    }
}
