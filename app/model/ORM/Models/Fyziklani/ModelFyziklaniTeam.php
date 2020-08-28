<?php

namespace FKSDB\ORM\Models\Fyziklani;

use FKSDB\Fyziklani\Closing\AlreadyClosedException;
use FKSDB\Fyziklani\Closing\NotCheckedSubmitsException;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\Models\Events\ModelFyziklaniParticipant;
use FKSDB\ORM\Models\IContestReferencedModel;
use FKSDB\ORM\Models\IEventReferencedModel;
use FKSDB\ORM\Models\ModelContest;
use FKSDB\ORM\Models\ModelEvent;
use FKSDB\ORM\Models\ModelPerson;
use FKSDB\ORM\Models\Schedule\ModelPersonSchedule;
use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\GroupedSelection;
use Nette\Security\IResource;

/**
 * @property-read  string category
 * @property-read  string name
 * @property-read  int e_fyziklani_team_id
 * @property-read  int event_id
 * @property-read  int points
 * @property-read  string status
 * @property-read  \DateTimeInterface created
 * @property-read  \DateTimeInterface modified
 * @property-read  string phone
 * @property-read  bool force_a
 * @property-read  string password
 * @property-read  ActiveRow event
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 * @author Michal Červeňák <miso@fykos.cz>
 *
 */
class ModelFyziklaniTeam extends AbstractModelSingle implements IEventReferencedModel, IResource, IContestReferencedModel {
    public const RESOURCE_ID = 'fyziklani.team';

    public function __toString(): string {
        return $this->name;
    }

    public function getContest(): ModelContest {
        return $this->getEvent()->getContest();
    }

    public function getTeacher(): ?ModelPerson {
        $row = $this->ref(DbNames::TAB_PERSON, 'teacher_id');
        if ($row) {
            return ModelPerson::createFromActiveRow($row);
        }
        return null;
    }

    public function getEvent(): ModelEvent {
        return ModelEvent::createFromActiveRow($this->event);
    }

    public function getParticipants(): GroupedSelection {
        return $this->related(DbNames::TAB_E_FYZIKLANI_PARTICIPANT, 'e_fyziklani_team_id');
    }

    public function getPosition(): ?ModelFyziklaniTeamPosition {
        $row = $this->related(DbNames::TAB_FYZIKLANI_TEAM_POSITION, 'e_fyziklani_team_id')->fetch();
        if ($row) {
            return ModelFyziklaniTeamPosition::createFromActiveRow($row);
        }
        return null;
    }

    /* ******************** SUBMITS ******************************* */

    public function getAllSubmits(): GroupedSelection {
        return $this->related(DbNames::TAB_FYZIKLANI_SUBMIT, 'e_fyziklani_team_id');
    }

    public function getNonRevokedSubmits(): GroupedSelection {
        return $this->getAllSubmits()->where('points IS NOT NULL');
    }

    public function getNonCheckedSubmits(): GroupedSelection {
        return $this->getNonRevokedSubmits()->where('state IS NULL OR state != ?', ModelFyziklaniSubmit::STATE_CHECKED);
    }

    public function hasAllSubmitsChecked(): bool {
        return $this->getNonCheckedSubmits()->count() === 0;
    }

    public function hasOpenSubmitting(): bool {
        return !is_numeric($this->points);
    }

    /**
     * @param bool $throws
     * @return bool
     * @throws AlreadyClosedException
     * @throws NotCheckedSubmitsException
     */
    public function canClose(bool $throws = true): bool {
        if (!$this->hasOpenSubmitting()) {
            if (!$throws) {
                return false;
            }
            throw new AlreadyClosedException($this);
        }
        if (!$this->hasAllSubmitsChecked()) {
            if (!$throws) {
                return false;
            }
            throw new NotCheckedSubmitsException($this);
        }
        return true;
    }

    /**
     * @param array $types
     * @return ModelPersonSchedule[]
     */
    public function getScheduleRest(array $types = ['accommodation', 'weekend']): array {
        $toPay = [];
        foreach ($this->getPersons() as $person) {
            $toPay[] = $person->getScheduleRests($this->getEvent(), $types);
        }
        return $toPay;
    }

    /**
     * @return ModelPerson[]
     */
    public function getPersons(): array {
        $persons = [];
        /** @var ModelFyziklaniParticipant $pRow */
        foreach ($this->getParticipants() as $pRow) {
            $persons[] = ModelPerson::createFromActiveRow($pRow->event_participant->person);
        }
        $teacher = $this->getTeacher();
        if ($teacher) {
            $persons[] = $teacher;
        }
        return $persons;
    }

    public function __toArray(bool $includePosition = false): array {
        $data = [
            'created' => $this->created->format('c'),
            'category' => $this->category,
            'name' => $this->name,
            'status' => $this->status,
            'teamId' => $this->e_fyziklani_team_id,
        ];
        $position = $this->getPosition();
        if ($includePosition && $position) {
            $data['x'] = $position->col;
            $data['y'] = $position->row;
            $data['roomId'] = $position->getRoom()->room_id;
        }
        return $data;
    }

    public function getResourceId(): string {
        return self::RESOURCE_ID;
    }
}
