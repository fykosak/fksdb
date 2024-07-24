<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models;

use FKSDB\Components\Game\GameException;
use FKSDB\Components\Game\NotSetGameParametersException;
use FKSDB\Components\Game\Submits\Handler\CtyrbojHandler;
use FKSDB\Components\Game\Submits\Handler\FOFHandler;
use FKSDB\Components\Game\Submits\Handler\Handler;
use FKSDB\Models\MachineCode\MachineCodeException;
use FKSDB\Models\ORM\DbNames;
use FKSDB\Models\ORM\Models\Fyziklani\GameSetupModel;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use FKSDB\Models\ORM\Models\Fyziklani\TeamState;
use FKSDB\Models\ORM\Models\Schedule\ScheduleGroupModel;
use FKSDB\Models\ORM\Services\ContestYearService;
use FKSDB\Models\ORM\Tests\Event\NoRoleSchedule;
use FKSDB\Models\ORM\Tests\Test;
use FKSDB\Models\WebService\NodeCreator;
use FKSDB\Models\WebService\XMLHelper;
use Fykosak\NetteORM\Model\Model;
use Fykosak\NetteORM\Selection\TypedGroupedSelection;
use Fykosak\Utils\Localization\LocalizedString;
use Nette\DI\Container;
use Nette\InvalidArgumentException;
use Nette\Neon\Neon;
use Nette\Schema\Processor;
use Nette\Security\Resource;
use Nette\Utils\DateTime;

/**
 * @property-read int $event_id
 * @property-read int $event_type_id
 * @property-read EventTypeModel $event_type
 * @property-read int $year
 * @property-read int $event_year
 * @property-read DateTime $begin
 * @property-read DateTime $end
 * @property-read DateTime $registration_begin
 * @property-read DateTime $registration_end
 * @property-read string $name
 * @property-read string|null $report_cs
 * @property-read string|null $report_en
 * @property-read LocalizedString $report
 * @property-read string|null $description_cs
 * @property-read string|null $description_en
 * @property-read LocalizedString $description
 * @property-read string|null $place
 * @property-read string|null $parameters
 * @phpstan-type SerializedEventModel array{
 *    eventId:int,
 *    year:int,
 *    eventYear:int,
 *    begin:string,
 *    end:string,
 *    registrationBegin:string,
 *    registrationEnd:string,
 *    report:string|null,
 *    reportNew:array<string,string>,
 *    description:array<string,string>,
 *    name:string,
 *    nameNew:array<string,string>,
 *    eventTypeId:int,
 *    place:string|null,
 *    contestId:int,
 *    game?:array{
 *        availablePoints: int[],
 *        tasksOnBoard: int,
 *        hardVisible: bool,
 *        begin:string,
 *        end:string,
 *        resultsVisible:bool,
 *    }
 * }
 */
final class EventModel extends Model implements Resource, NodeCreator
{

    private const TEAM_EVENTS = [1, 9, 13, 17];
    public const RESOURCE_ID = 'event';
    private const POSSIBLY_ATTENDING_STATES = [
        TeamState::Participated,
        TeamState::Spare,
        TeamState::Applied,
        TeamState::Arrived,
    ];

    public function getContestYear(): ContestYearModel
    {
        return $this->event_type->contest->getContestYear($this->year);
    }

    /**
     * @throws NotSetGameParametersException
     */
    public function getGameSetup(): GameSetupModel
    {
        /** @var GameSetupModel|null $gameSetupRow */
        $gameSetupRow = $this->related(DbNames::TAB_FYZIKLANI_GAME_SETUP, 'event_id')->fetch();
        if (!$gameSetupRow) {
            throw new NotSetGameParametersException();
        }
        return $gameSetupRow;
    }

    /**
     * @phpstan-return TypedGroupedSelection<ScheduleGroupModel>
     */
    public function getScheduleGroups(): TypedGroupedSelection
    {
        /** @phpstan-var TypedGroupedSelection<ScheduleGroupModel> $selection */
        $selection = $this->related(DbNames::TAB_SCHEDULE_GROUP, 'event_id');
        return $selection;
    }

    public function hasSchedule(): bool
    {
        return (bool)$this->getScheduleGroups()->count('*');
    }

    /**
     * @phpstan-return TypedGroupedSelection<EventParticipantModel>
     */
    public function getParticipants(): TypedGroupedSelection
    {
        /** @phpstan-var TypedGroupedSelection<EventParticipantModel> $selection */
        $selection = $this->related(DbNames::TAB_EVENT_PARTICIPANT, 'event_id');
        return $selection;
    }

    /**
     * @phpstan-return TypedGroupedSelection<EventParticipantModel>
     */
    public function getPossiblyAttendingParticipants(): TypedGroupedSelection
    {
        /** @phpstan-var TypedGroupedSelection<EventParticipantModel> $selection */
        $selection = $this->getParticipants()->where('status', self::POSSIBLY_ATTENDING_STATES);
        return $selection;
    }

    /**
     * @phpstan-return TypedGroupedSelection<TeamModel2>
     */
    public function getTeams(): TypedGroupedSelection
    {
        /** @phpstan-var TypedGroupedSelection<TeamModel2> $selection */
        $selection = $this->related(DbNames::TAB_FYZIKLANI_TEAM, 'event_id');
        return $selection;
    }

    /**
     * @phpstan-return TypedGroupedSelection<TeamModel2>
     */
    public function getParticipatingTeams(): TypedGroupedSelection
    {
        /** @phpstan-var TypedGroupedSelection<TeamModel2> $selection */
        $selection = $this->getTeams()->where('state', TeamState::Participated);
        return $selection;
    }

    /**
     * @phpstan-return TypedGroupedSelection<TeamModel2>
     */
    public function getPossiblyAttendingTeams(): TypedGroupedSelection
    {
        /** @phpstan-var TypedGroupedSelection<TeamModel2> $selection */
        $selection = $this->getTeams()->where('state', self::POSSIBLY_ATTENDING_STATES);
        return $selection;
    }

    /**
     * @phpstan-return TypedGroupedSelection<EventOrganizerModel>
     */
    public function getEventOrganizers(): TypedGroupedSelection
    {
        /** @phpstan-var TypedGroupedSelection<EventOrganizerModel> $selection */
        $selection = $this->related(DbNames::TAB_EVENT_ORGANIZER, 'event_id');
        return $selection;
    }

    /**
     * @phpstan-return TypedGroupedSelection<\FKSDB\Models\ORM\Models\Fyziklani\TaskModel>
     */
    public function getTasks(): TypedGroupedSelection
    {
        /** @phpstan-var TypedGroupedSelection<\FKSDB\Models\ORM\Models\Fyziklani\TaskModel> $selection */
        $selection = $this->related(DbNames::TAB_FYZIKLANI_TASK, 'event_id');
        return $selection;
    }

    /**
     * @return mixed
     * @throws \ReflectionException
     */
    public function &__get(string $key) // phpcs:ignore
    {
        switch ($key) {
            case 'report':
                $value = new LocalizedString([
                    'cs' => $this->report_cs,
                    'en' => $this->report_en,
                ]);
                break;
            case 'description':
                $value = new LocalizedString([
                    'cs' => $this->description_cs,
                    'en' => $this->description_en,
                ]);
                break;
            default:
                $value = parent::__get($key);
        }
        return $value;
    }

    public function getResourceId(): string
    {
        return self::RESOURCE_ID;
    }

    /**
     * @phpstan-return LocalizedString<'cs'|'en'>
     */
    public function getName(): LocalizedString
    {
        switch ($this->event_type_id) {
            case 4:
            case 5:
                return new LocalizedString([
                    'cs' => $this->event_type->name . ' ' . $this->place, // TODO
                    'en' => $this->event_type->name . ' ' . $this->place, // TODO
                ]);
            case 1:
                return new LocalizedString([
                    'cs' => 'Fyziklání ' . $this->begin->format('Y'),
                    'en' => 'Fyziklani ' . $this->begin->format('Y'),
                ]);
            case 2:
            case 14:
                return new LocalizedString([
                    'cs' => 'DSEF ' .
                        ($this->begin->format('m') < ContestYearService::FIRST_AC_MONTH ? 'jaro' : 'podzim') . ' ' .
                        $this->begin->format('Y'),
                    'en' => 'DSEF ' .
                        ($this->begin->format('m') < ContestYearService::FIRST_AC_MONTH ? 'spring' : 'autumn') . ' ' .
                        $this->begin->format('Y'),
                ]);
            case 9:
                return new LocalizedString([
                    'cs' => 'Fyziklání Online ' . $this->begin->format('Y'),
                    'en' => 'Physics Brawl Online ' . $this->begin->format('Y'),
                ]);
            default:
                return new LocalizedString([
                    'cs' => $this->name,
                    'en' => $this->name,
                ]);
        }
    }

    public function isTeamEvent(): bool
    {
        return in_array($this->event_type_id, self::TEAM_EVENTS);
    }

    /**
     * @phpstan-return SerializedEventModel
     */
    public function __toArray(): array
    {
        $data = [
            'eventId' => $this->event_id,
            'year' => $this->year,
            'eventYear' => $this->event_year,
            'begin' => $this->begin->format('c'),
            'end' => $this->end->format('c'),
            'registrationBegin' => $this->registration_begin->format('c'),
            'registrationEnd' => $this->registration_end->format('c'),
            'registration' => [
                'begin' => $this->registration_begin->format('c'),
                'end' => $this->registration_end->format('c'),
            ],
            'report' => $this->report_cs,
            'reportNew' => $this->report->__serialize(),
            'description' => $this->description->__serialize(),
            'name' => $this->name,
            'nameNew' => $this->getName()->__serialize(),
            'eventTypeId' => $this->event_type_id,
            'place' => $this->place,
            'contestId' => $this->event_type->contest_id,
        ];
        try {
            $gameSetup = $this->getGameSetup();
            $data['game'] = [
                'availablePoints' => $gameSetup->getAvailablePoints(),
                'tasksOnBoard' => $gameSetup->tasks_on_board,
                'hardVisible' => (bool)$gameSetup->result_hard_display,
                'begin' => $gameSetup->game_start->format('c'),
                'end' => $gameSetup->game_end->format('c'),
                'resultsVisible' => $gameSetup->isResultsVisible(),
            ];
        } catch (NotSetGameParametersException $exception) {
        }
        return $data;
    }

    /**
     * @throws \DOMException
     */
    public function createXMLNode(\DOMDocument $document): \DOMElement
    {
        $node = $document->createElement('event');
        $node->setAttribute('eventId', (string)$this->event_id);
        XMLHelper::fillArrayToNode($this->__toArray(), $document, $node, true);
        return $node;
    }

    public function isRegistrationOpened(): bool
    {
        return ($this->registration_begin->getTimestamp() <= time())
            && ($this->registration_end->getTimestamp() >= time());
    }

    /**
     * @throws MachineCodeException
     */
    public function getSalt(): string
    {
        switch ($this->event_type_id) {
            case 1:
            case 2:
            case 14:
                $salt = $this->getParameter('hashSalt');
                break;
            default:
                throw new MachineCodeException(_('Not implemented'));
        }
        if (!$salt) {
            throw new MachineCodeException(_('Empty salt'));
        }
        return (string)$salt;
    }

    public function createGameHandler(Container $container): Handler
    {
        switch ($this->event_type_id) {
            case 1:
                return new FOFHandler($container);
            case 17:
                return new CtyrbojHandler($container);
        }
        throw new GameException(_('Game handler does not exist for this event'));
    }

    /**
     * @return mixed
     */
    public function getParameter(string $name)
    {
        try {
            $parameters = $this->parameters ? Neon::decode($this->parameters) : [];
            $processor = new Processor();
            return $processor->process($this->event_type->getParamSchema(), $parameters)[$name] ?? null;
        } catch (InvalidArgumentException $exception) {
            throw new InvalidArgumentException(
                sprintf('No parameter "%s" for event %s.', $name, $this->name),
                0,
                $exception
            );
        }
    }

    /**
     * @phpstan-return Test<self>[]
     */
    public static function getTests(Container $container): array
    {
        return [
            new NoRoleSchedule($container),
        ];
    }
}
