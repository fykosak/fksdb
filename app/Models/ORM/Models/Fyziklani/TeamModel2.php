<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models\Fyziklani;

use FKSDB\Components\Game\Closing\AlreadyClosedException;
use FKSDB\Components\Game\Closing\NotCheckedSubmitsException;
use FKSDB\Models\MachineCode\MachineCode;
use FKSDB\Models\ORM\DbNames;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Models\Schedule\PersonScheduleModel;
use FKSDB\Models\ORM\Models\Schedule\ScheduleGroupType;
use FKSDB\Models\ORM\Tests\Event\Team\CategoryCheck;
use FKSDB\Models\ORM\Tests\Event\Team\PendingTeams;
use FKSDB\Models\ORM\Tests\Event\Team\TeamsPerSchool;
use FKSDB\Models\ORM\Tests\Test;
use FKSDB\Models\WebService\XMLHelper;
use Fykosak\NetteORM\Model\Model;
use Fykosak\NetteORM\Selection\TypedGroupedSelection;
use Nette\DI\Container;
use Nette\Security\Resource;

/**
 * @property-read int $fyziklani_team_id
 * @property-read int $event_id
 * @property-read EventModel $event
 * @property-read string $name
 * @property-read TeamState $state
 * @property-read TeamCategory $category
 * @property-read \DateTimeInterface $created
 * @property-read string|null $phone
 * @property-read string|null $note
 * @property-read string|null $password
 * @property-read int|null $points
 * @property-read int|null $rank_total
 * @property-read int|null $rank_category
 * @property-read int|null $force_a
 * @property-read GameLang|null $game_lang
 * @property-read TeamScholarship $scholarship
 * @phpstan-type SerializedTeamModel array{
 *      teamId:int,
 *      name:string,
 *      status:string,
 *      code:string|null,
 *      category:string,
 *      created:string,
 *      phone:string|null,
 *      points:int|null,
 *      rankCategory:int|null,
 *      rankTotal:int|null,
 *      forceA:int|null,
 *      gameLang:string|null,
 * }
 */
final class TeamModel2 extends Model implements Resource
{
    public const RESOURCE_ID = 'fyziklani.team';

    /**
     * @phpstan-return TypedGroupedSelection<TeamTeacherModel>
     */
    public function getTeachers(): TypedGroupedSelection
    {
        /** @phpstan-var TypedGroupedSelection<TeamTeacherModel> $selection */
        $selection = $this->related(DbNames::TAB_FYZIKLANI_TEAM_TEACHER, 'fyziklani_team_id');
        return $selection;
    }

    /**
     * @phpstan-return TypedGroupedSelection<TeamMemberModel>
     */
    public function getMembers(): TypedGroupedSelection
    {
        /** @phpstan-var TypedGroupedSelection<TeamMemberModel> $selection */
        $selection = $this->related(DbNames::TAB_FYZIKLANI_TEAM_MEMBER, 'fyziklani_team_id');
        return $selection;
    }

    /**
     * @phpstan-return TypedGroupedSelection<SubmitModel>
     */
    public function getSubmits(): TypedGroupedSelection
    {
        /** @phpstan-var TypedGroupedSelection<SubmitModel> $selection */
        $selection = $this->related(DbNames::TAB_FYZIKLANI_SUBMIT, 'fyziklani_team_id');
        return $selection;
    }

    /**
     * @phpstan-return TypedGroupedSelection<SubmitModel>
     */
    public function getNonRevokedSubmits(): TypedGroupedSelection
    {
        return $this->getSubmits()->where('points IS NOT NULL');
    }

    /**
     * @phpstan-return TypedGroupedSelection<SubmitModel>
     */
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

    public function getSubmit(TaskModel $task): ?SubmitModel
    {
        /** @var SubmitModel|null $submit */
        $submit = $this->getSubmits()->where('fyziklani_task_id', $task->fyziklani_task_id)->fetch();
        return $submit;
    }

    /**
     * @phpstan-param string[] $types
     * @phpstan-return PersonScheduleModel[][]
     */
    public function getScheduleRest(
        array $types = [ScheduleGroupType::Accommodation, ScheduleGroupType::Weekend]
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
     * @phpstan-return PersonModel[]
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
    public function &__get(string $key) // phpcs:ignore
    {
        $value = parent::__get($key);
        switch ($key) {
            case 'state':
                $value = TeamState::from($value);
                break;
            case 'category':
                $value = TeamCategory::from($value);
                break;
            case 'game_lang':
                $value = GameLang::tryFrom($value);
                break;
            case 'scholarship':
                $value = TeamScholarship::from($value);
                break;
        }
        return $value;
    }

    public function createMachineCode(): ?string
    {
        try {
            return MachineCode::createHash($this, $this->event->getSalt());
        } catch (\Throwable $exception) {
            return null;
        }
    }

    /**
     * @phpstan-return SerializedTeamModel
     */
    public function __toArray(): array
    {
        return [
            'teamId' => $this->fyziklani_team_id,
            'name' => $this->name,
            'code' => $this->createMachineCode(),
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

    /**
     * @phpstan-return Test<TeamModel2>[]
     */
    public static function getTests(Container $container): array
    {
        return [
            new CategoryCheck($container),
            new PendingTeams($container),
            new TeamsPerSchool($container),
        ];
    }
}
