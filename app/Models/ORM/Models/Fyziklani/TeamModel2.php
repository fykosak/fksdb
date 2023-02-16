<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models\Fyziklani;

use FKSDB\Components\Game\Closing\AlreadyClosedException;
use FKSDB\Components\Game\Closing\NotCheckedSubmitsException;
use FKSDB\Models\ORM\DbNames;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\Fyziklani\Seating\TeamSeatModel;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Models\Schedule\PersonScheduleModel;
use FKSDB\Models\ORM\Models\Schedule\ScheduleGroupType;
use FKSDB\Models\WebService\XMLHelper;
use Fykosak\NetteORM\Model;
use Fykosak\NetteORM\TypedGroupedSelection;
use Nette\Security\Resource;

/**
 * @property-read int fyziklani_team_id
 * @property-read int event_id
 * @property-read EventModel event
 * @property-read string name
 * @property-read TeamState state
 * @property-read TeamCategory category
 * @property-read \DateTimeInterface created
 * @property-read string phone
 * @property-read string note
 * @property-read string password
 * @property-read int points
 * @property-read int rank_total
 * @property-read int rank_category
 * @property-read int force_a
 * @property-read GameLang game_lang
 */
class TeamModel2 extends Model implements Resource
{
    public const RESOURCE_ID = 'fyziklani.team';

    public function getTeachers(): TypedGroupedSelection
    {
        return $this->related(DbNames::TAB_FYZIKLANI_TEAM_TEACHER, 'fyziklani_team_id');
    }

    public function getMembers(): TypedGroupedSelection
    {
        return $this->related(DbNames::TAB_FYZIKLANI_TEAM_MEMBER, 'fyziklani_team_id');
    }

    public function getTeamSeat(): ?TeamSeatModel
    {
        return $this->related(DbNames::TAB_FYZIKLANI_TEAM_SEAT, 'fyziklani_team_id')->fetch();
    }

    /* ******************** SUBMITS ******************************* */

    public function getSubmits(): TypedGroupedSelection
    {
        return $this->related(DbNames::TAB_FYZIKLANI_SUBMIT, 'fyziklani_team_id');
    }

    public function getNonRevokedSubmits(): TypedGroupedSelection
    {
        return $this->getSubmits()->where('points IS NOT NULL');
    }

    public function getNonCheckedSubmits(): TypedGroupedSelection
    {
        return $this->getNonRevokedSubmits()->where('state IS NULL OR state != ?', SubmitState::CHECKED);
    }

    public function hasAllSubmitsChecked(): bool
    {
        return $this->getNonCheckedSubmits()->count('*') === 0;
    }

    public function hasOpenSubmitting(): bool
    {
        return is_null($this->points);
    }

    /**
     * @throws AlreadyClosedException
     * @throws NotCheckedSubmitsException
     */
    public function canClose(): void
    {
        if (!$this->hasOpenSubmitting()) {
            throw new AlreadyClosedException($this);
        }
        if (!$this->hasAllSubmitsChecked()) {
            throw new NotCheckedSubmitsException($this);
        }
    }

    /**
     * @return PersonScheduleModel[]
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
     * @return PersonModel[]
     */
    public function getPersons(): array
    {
        $persons = [];
        /** @var TeamMemberModel $pRow */
        foreach ($this->getMembers() as $pRow) {
            $persons[] = $pRow->person;
        }
        /** @var TeamTeacherModel $pRow */
        foreach ($this->getTeachers() as $pRow) {
            $persons[] = $pRow->person;
        }
        return $persons;
    }

    /**
     * @param string $key
     * @return GameLang|TeamCategory|TeamState|mixed|null
     * @throws \ReflectionException
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

    /**
     * @throws \DOMException
     */
    public function createXMLNode(\DOMDocument $document): \DOMElement
    {
        $node = $document->createElement('team');
        $node->setAttribute('teamId', (string)$this->fyziklani_team_id);
        XMLHelper::fillArrayToNode($this->__toArray(), $document, $node);
        return $node;
    }

    public function getResourceId(): string
    {
        return self::RESOURCE_ID;
    }
}
