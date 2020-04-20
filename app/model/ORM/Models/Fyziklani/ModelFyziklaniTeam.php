<?php

namespace FKSDB\ORM\Models\Fyziklani;

use FKSDB\model\Fyziklani\ClosedSubmittingException;
use FKSDB\model\Fyziklani\NotCheckedSubmitsException;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\Models\IEventReferencedModel;
use FKSDB\ORM\Models\ModelEvent;
use FKSDB\ORM\Models\ModelPerson;
use FKSDB\ORM\Models\Schedule\ModelPersonSchedule;
use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\GroupedSelection;
use Nette\Security\IResource;
use Nette\Utils\DateTime;

/**
 * @property-read  string category
 * @property-read  string name
 * @property-read  integer e_fyziklani_team_id
 * @property-read  integer event_id
 * @property-read  integer points
 * @property-read  string status
 * @property-read  DateTime created
 * @property-read  DateTime modified
 * @property-read  string phone
 * @property-read  bool force_a
 * @property-read  string password
 * @property-read  ActiveRow event
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 * @author Michal Červeňák <miso@fykos.cz>
 *
 */
class ModelFyziklaniTeam extends AbstractModelSingle implements IEventReferencedModel, IResource {
    const RESOURCE_ID = 'fyziklani.team';

    /**
     * @return string
     */
    public function __toString(): string {
        return $this->name;
    }

    /**
     * @return ModelPerson|NULL
     */
    public function getTeacher() {
        $row = $this->ref(DbNames::TAB_PERSON, 'teacher_id');
        if ($row) {
            return ModelPerson::createFromActiveRow($row);
        }
        return null;
    }

    /**
     * @return ModelEvent
     */
    public function getEvent(): ModelEvent {
        return ModelEvent::createFromActiveRow($this->event);
    }

    /**
     * @return GroupedSelection
     */
    public function getParticipants(): GroupedSelection {
        return $this->related(DbNames::TAB_E_FYZIKLANI_PARTICIPANT, 'e_fyziklani_team_id');
    }

    /**
     * @return null|ModelFyziklaniTeamPosition
     */
    public function getPosition() {
        $row = $this->related(DbNames::TAB_FYZIKLANI_TEAM_POSITION, 'e_fyziklani_team_id')->fetch();
        if ($row) {
            return ModelFyziklaniTeamPosition::createFromActiveRow($row);
        }
        return null;
    }
    /* ******************** SUBMITS ******************************* */

    /**
     * @return GroupedSelection
     */
    public function getAllSubmits(): GroupedSelection {
        return $this->related(DbNames::TAB_FYZIKLANI_SUBMIT, 'e_fyziklani_team_id');
    }

    /**
     * @return GroupedSelection
     */
    public function getNonRevokedSubmits(): GroupedSelection {
        return $this->getAllSubmits()->where('points IS NOT NULL');
    }

    /**
     * @return GroupedSelection
     */
    public function getNonCheckedSubmits(): GroupedSelection {
        return $this->getNonRevokedSubmits()->where('state IS NULL OR state != ?', ModelFyziklaniSubmit::STATE_CHECKED);
    }

    /**
     * @return bool
     */
    public function hasAllSubmitsChecked(): bool {
        return $this->getNonCheckedSubmits()->count() === 0;
    }


    /**
     * @return bool
     */
    public function hasOpenSubmitting(): bool {
        $points = $this->points;
        return !is_numeric($points);
    }

    /**
     * @return bool
     */
    public function isReadyForClosing(): bool {
        return $this->hasAllSubmitsChecked() && $this->hasOpenSubmitting();
    }

    /**
     * @return bool
     * @throws ClosedSubmittingException
     * @throws NotCheckedSubmitsException
     */
    public function canClose(): bool {
        if (!$this->hasOpenSubmitting()) {
            throw new ClosedSubmittingException($this);
        }
        if (!$this->hasAllSubmitsChecked()) {
            throw new NotCheckedSubmitsException;
        }
        return true;
    }

    /**
     * @param array $types
     * @return ModelPersonSchedule[]
     */
    public function getScheduleRest(array $types = ['accommodation', 'weekend']): array {
        $toPay = [];
        /** @var ModelPerson $person */
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
        foreach ($this->getParticipants() as $pRow) {
            $persons[] = ModelPerson::createFromActiveRow($pRow->event_participant->person);
        }
        $teacher = $this->getTeacher();
        if ($teacher) {
            $persons[] = $teacher;
        }
        return $persons;
    }

    /**
     * @param bool $includePosition
     * @return array
     */
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

    /**
     * Returns a string identifier of the Resource.
     * @return string
     */
    public function getResourceId() {
        return self::RESOURCE_ID;
    }
}
