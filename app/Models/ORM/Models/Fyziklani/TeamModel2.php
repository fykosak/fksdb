<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models\Fyziklani;

use FKSDB\Models\Fyziklani\Closing\AlreadyClosedException;
use FKSDB\Models\Fyziklani\Closing\NotCheckedSubmitsException;
use FKSDB\Models\ORM\DbNames;
use FKSDB\Models\ORM\Models\Fyziklani\Seating\TeamSeatModel;
use FKSDB\Models\ORM\Models\ModelContest;
use FKSDB\Models\ORM\Models\ModelEvent;
use FKSDB\Models\ORM\Models\ModelPerson;
use FKSDB\Models\ORM\Models\Schedule\ModelPersonSchedule;
use FKSDB\Models\ORM\Models\Schedule\ScheduleGroupType;
use FKSDB\Models\WebService\XMLHelper;
use Fykosak\NetteORM\Model;
use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\GroupedSelection;
use Nette\Security\Resource;

/**
 * @property-read TeamCategory category
 * @property-read string name
 * @property-read int fyziklani_team_id
 * @property-read int event_id
 * @property-read int points
 * @property-read TeamState state
 * @property-read \DateTimeInterface created
 * @property-read string phone
 * @property-read bool force_a
 * @property-read string password
 * @property-read ModelEvent event
 * @property-read GameLang game_lang
 * @property-read int rank_category
 * @property-read int rank_total
 */
class TeamModel2 extends Model implements Resource
{
    public const RESOURCE_ID = 'fyziklani.team';

    public function getContest(): ModelContest
    {
        return $this->event->getContest();
    }

    public function getTeachers(): GroupedSelection
    {
        return $this->related(DbNames::TAB_FYZIKLANI_TEAM_TEACHER);
    }

    public function getMembers(): GroupedSelection
    {
        return $this->related(DbNames::TAB_FYZIKLANI_TEAM_MEMBER, 'fyziklani_team_id');
    }

    public function getTeamSeat(): ?TeamSeatModel
    {
        $row = $this->related(DbNames::TAB_FYZIKLANI_TEAM_SEAT, 'fyziklani_team_id')->fetch();
        return $row ? TeamSeatModel::createFromActiveRow($row, $this->mapper) : null;
    }

    /* ******************** SUBMITS ******************************* */

    public function getAllSubmits(): GroupedSelection
    {
        return $this->related(DbNames::TAB_FYZIKLANI_SUBMIT, 'fyziklani_team_id');
    }

    public function getNonRevokedSubmits(): GroupedSelection
    {
        return $this->getAllSubmits()->where('points IS NOT NULL');
    }

    public function getNonCheckedSubmits(): GroupedSelection
    {
        return $this->getNonRevokedSubmits()->where('state IS NULL OR state != ?', SubmitModel::STATE_CHECKED);
    }

    public function hasAllSubmitsChecked(): bool
    {
        return $this->getNonCheckedSubmits()->count() === 0;
    }

    public function hasOpenSubmitting(): bool
    {
        return is_null($this->points);
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
    public function getScheduleRest(
        array $types = [ScheduleGroupType::ACCOMMODATION, ScheduleGroupType::WEEKEND]
    ): array {
        $toPay = [];
        foreach ($this->getPersons() as $person) {
            $rest = $person->getScheduleRests($this->event, $types);
            if (count($rest)) {
                $toPay[] = $rest;
            }
        }
        return $toPay;
    }

    /**
     * @return ModelPerson[]
     */
    public function getPersons(): array
    {
        $persons = [];
        foreach ($this->getMembers() as $pRow) {
            $persons[] = TeamMemberModel::createFromActiveRow($pRow, $this->mapper)->getPerson();
        }
        foreach ($this->getTeachers() as $pRow) {
            $persons[] = TeamTeacherModel::createFromActiveRow($pRow, $this->mapper)->getPerson();
        }
        return $persons;
    }

    /**
     * @return mixed
     */
    public function &__get(string $key)
    {
        $value = parent::__get($key);
        switch ($key) {
            case 'state':
                $value = TeamState::tryFrom($value);
                break;
            case 'category':
                $value = TeamCategory::tryFrom($value);
                break;
            case 'game_lang':
                $value = GameLang::tryFrom($value);
                break;
        }
        return $value;
    }

    public function __toArray(): array
    {
        return [
            'teamId' => $this->fyziklani_team_id,
            'name' => $this->name,
            'status' => $this->state->value,
            'category' => $this->category->value,
            'created' => $this->created->format('c'),
            'phone' => $this->phone,
            'points' => $this->points,
            'rankCategory' => $this->rank_category,
            'rankTotal' => $this->rank_total,
            'forceA' => $this->force_a,
            'gameLang' => $this->game_lang->value,
        ];
    }

    public function createXMLNode(\DOMDocument $document): \DOMElement
    {
        $node = $document->createElement('team');
        $node->setAttribute('teamId', (string)$this->fyziklani_team_id);
        XMLHelper::fillArrayToNode([
            'teamId' => $this->fyziklani_team_id,
            'name' => $this->name,
            'status' => $this->state->value,
            'category' => $this->category->value,
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
    }

    public function getResourceId(): string
    {
        return self::RESOURCE_ID;
    }
}
