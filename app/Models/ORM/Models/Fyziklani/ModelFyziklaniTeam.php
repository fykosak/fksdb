<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models\Fyziklani;

use FKSDB\Models\Fyziklani\Closing\AlreadyClosedException;
use FKSDB\Models\Fyziklani\Closing\NotCheckedSubmitsException;
use FKSDB\Models\ORM\DbNames;
use FKSDB\Models\ORM\Models\Events\ModelFyziklaniParticipant;
use FKSDB\Models\ORM\Models\ModelContest;
use FKSDB\Models\ORM\Models\ModelEvent;
use FKSDB\Models\ORM\Models\ModelPerson;
use FKSDB\Models\ORM\Models\Schedule\ModelPersonSchedule;
use FKSDB\Models\WebService\NodeCreator;
use FKSDB\Models\WebService\XMLHelper;
use Fykosak\NetteORM\AbstractModel;
use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\GroupedSelection;
use Nette\Security\Resource;

/**
 * @property-read string category
 * @property-read string name
 * @property-read int e_fyziklani_team_id
 * @property-read int event_id
 * @property-read int points
 * @property-read string status
 * @property-read \DateTimeInterface created
 * @property-read \DateTimeInterface modified
 * @property-read string phone
 * @property-read bool force_a
 * @property-read string password
 * @property-read ActiveRow event
 * @property-read string game_lang
 * @property-read int rank_category
 * @property-read int rank_total
 * @property-read int teacher_id
 * @property-read ActiveRow person
 */
class ModelFyziklaniTeam extends AbstractModel implements Resource, NodeCreator
{

    public const RESOURCE_ID = 'fyziklani.team';
    public const CATEGORY_HIGH_SCHOOL_A = 'A';
    public const CATEGORY_HIGH_SCHOOL_B = 'B';
    public const CATEGORY_HIGH_SCHOOL_C = 'C';
    public const CATEGORY_ABROAD = 'F';
    public const CATEGORY_OPEN = 'O';

    public function __toString(): string
    {
        return $this->name;
    }

    public function getContest(): ModelContest
    {
        return $this->getEvent()->getContest();
    }

    public function getTeacher(): ?ModelPerson
    {
        return isset($this->teacher_id) ? ModelPerson::createFromActiveRow($this->ref('person', 'teacher_id')) : null;
    }

    public function getEvent(): ModelEvent
    {
        return ModelEvent::createFromActiveRow($this->event);
    }

    public function getFyziklaniParticipants(): GroupedSelection
    {
        return $this->related(DbNames::TAB_E_FYZIKLANI_PARTICIPANT, 'e_fyziklani_team_id');
    }

    public function getPosition(): ?ModelFyziklaniTeamPosition
    {
        $row = $this->related(DbNames::TAB_FYZIKLANI_TEAM_POSITION, 'e_fyziklani_team_id')->fetch();
        return $row ? ModelFyziklaniTeamPosition::createFromActiveRow($row) : null;
    }

    /* ******************** SUBMITS ******************************* */

    public function getAllSubmits(): GroupedSelection
    {
        return $this->related(DbNames::TAB_FYZIKLANI_SUBMIT, 'e_fyziklani_team_id');
    }

    public function getNonRevokedSubmits(): GroupedSelection
    {
        return $this->getAllSubmits()->where('points IS NOT NULL');
    }

    public function getNonCheckedSubmits(): GroupedSelection
    {
        return $this->getNonRevokedSubmits()->where('state IS NULL OR state != ?', ModelFyziklaniSubmit::STATE_CHECKED);
    }

    public function hasAllSubmitsChecked(): bool
    {
        return $this->getNonCheckedSubmits()->count() === 0;
    }

    public function hasOpenSubmitting(): bool
    {
        return !isset($this->points);
    }

    /**
     * @throws AlreadyClosedException
     * @throws NotCheckedSubmitsException
     */
    public function canClose(bool $throws = true): bool
    {
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
     * @return ModelPersonSchedule[]
     */
    public function getScheduleRest(array $types = ['accommodation', 'weekend']): array
    {
        $toPay = [];
        foreach ($this->getPersons() as $person) {
            $toPay[] = $person->getScheduleRests($this->getEvent(), $types);
        }
        return $toPay;
    }

    /**
     * @return ModelPerson[]
     */
    public function getPersons(): array
    {
        $persons = [];
        /** @var ModelFyziklaniParticipant $pRow */
        foreach ($this->getFyziklaniParticipants() as $pRow) {
            $persons[] = ModelFyziklaniParticipant::createFromActiveRow($pRow)->getEventParticipant()->getPerson();
        }
        $teacher = $this->getTeacher();
        if ($teacher) {
            $persons[] = $teacher;
        }
        return $persons;
    }

    public function __toArray(bool $includePosition = false, bool $includePassword = false): array
    {
        $data = [
            'created' => $this->created->format('c'),
            'category' => $this->category,
            'name' => $this->name,
            'status' => $this->status,
            'teamId' => $this->e_fyziklani_team_id,
            'gameLang' => $this->game_lang,
            'points' => $this->points,
        ];
        if ($includePassword) {
            $data['password'] = $this->password;
        }
        $position = $this->getPosition();
        if ($includePosition && $position) {
            $data['x'] = $position->col;
            $data['y'] = $position->row;
            $data['roomId'] = $position->getRoom()->room_id;
        }
        return $data;
    }

    public function createXMLNode(\DOMDocument $document): \DOMElement
    {
        $node = $document->createElement('team');
        $node->setAttribute('teamId', (string)$this->e_fyziklani_team_id);
        XMLHelper::fillArrayToNode([
            'teamId' => $this->e_fyziklani_team_id,
            'name' => $this->name,
            'status' => $this->status,
            'category' => $this->category,
            'created' => $this->created->format('c'),
            'phone' => $this->phone,
            'password' => $this->password,
            'points' => $this->points,
            'rankCategory' => $this->rank_category,
            'rankTotal' => $this->rank_total,
            'forceA' => $this->force_a,
            'gameLang' => $this->game_lang,
        ], $document, $node);
        return $node;

        // `teacher_id`           INT(11)     NULL     DEFAULT NULL
        // `teacher_accomodation` TINYINT(1)  NOT NULL DEFAULT 0,
        // `teacher_present`      TINYINT(1)  NOT NULL DEFAULT 0,
        // `teacher_schedule`     TEXT        NULL     DEFAULT NULL
        // `note`                 TEXT        NULL     DEFAULT NULL,
    }

    public function getResourceId(): string
    {
        return self::RESOURCE_ID;
    }

    public static function mapCategoryToName(string $category): string
    {
        switch ($category) {
            case self::CATEGORY_HIGH_SCHOOL_A :
                return _('High-school students A');
            case self::CATEGORY_HIGH_SCHOOL_B :
                return _('High-school students B');
            case self::CATEGORY_HIGH_SCHOOL_C :
                return _('High-school students C');
            case self::CATEGORY_ABROAD :
                return _('Abroad high-school students');
            case self::CATEGORY_OPEN :
                return _('Open');
            default:
                throw new \InvalidArgumentException();
        }
    }
}
