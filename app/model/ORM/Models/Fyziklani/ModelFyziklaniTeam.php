<?php

namespace FKSDB\ORM\Models\Fyziklani;

use FKSDB\model\Fyziklani\ClosedSubmittingException;
use FKSDB\model\Fyziklani\NotCheckedSubmitsException;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\Models\IEventReferencedModel;
use FKSDB\ORM\Models\ModelEvent;
use FKSDB\ORM\Models\ModelPayment;
use FKSDB\ORM\Models\ModelPerson;
use FKSDB\ORM\Models\Schedule\ModelPersonSchedule;
use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\Selection;
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
     * @return Selection
     */
    public function getParticipants(): Selection {
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
     * @return Selection
     */
    public function getAllSubmits(): Selection {
        return $this->related(DbNames::TAB_FYZIKLANI_SUBMIT, 'e_fyziklani_team_id');
    }

    /**
     * @return Selection
     */
    public function getNonRevokedSubmits(): Selection {
        return $this->getAllSubmits()->where('points IS NOT NULL');
    }

    /**
     * @return Selection
     */
    public function getNonCheckedSubmits(): Selection {
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
            throw new NotCheckedSubmitsException();
        }
        return true;
    }

    /**
     * @param array $types
     * @return ModelPersonSchedule[]
     */
    public function getScheduleRest(array $types = ['accommodation', 'weekend']): array {
        $persons = [];
        foreach ($this->getParticipants() as $pRow) {
            $persons[] = ModelPerson::createFromActiveRow($pRow->event_participant->person);
        }
        $teacher = $this->getTeacher();
        if ($teacher) {
            $persons[] = $teacher;
        }
        $toPay = [];
        /**
         * @var ModelPerson $person
         */
        foreach ($persons as $person) {
            $schedule = $person->getScheduleForEvent($this->getEvent())
                ->where('schedule_item.schedule_group.schedule_group_type', $types)
                ->where('schedule_item.price_czk IS NOT NULL');
            foreach ($schedule as $pSchRow) {
                $pSchedule = ModelPersonSchedule::createFromActiveRow($pSchRow);
                $payment = $pSchedule->getPayment();
                if (!$payment || $payment->state !== ModelPayment::STATE_RECEIVED) {
                    $toPay[] = $pSchedule;
                }
            }
        }
        return $toPay;
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
