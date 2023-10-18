<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models;

use FKSDB\Components\Game\GameException;
use FKSDB\Components\Game\NotSetGameParametersException;
use FKSDB\Components\Game\Submits\Handler\CtyrbojHandler;
use FKSDB\Components\Game\Submits\Handler\FOFHandler;
use FKSDB\Components\Game\Submits\Handler\Handler;
use FKSDB\Models\ORM\DbNames;
use FKSDB\Models\ORM\Models\Fyziklani\GameSetupModel;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use FKSDB\Models\ORM\Models\Fyziklani\TeamState;
use FKSDB\Models\ORM\Models\Schedule\ScheduleGroupModel;
use FKSDB\Models\ORM\Services\ContestYearService;
use FKSDB\Models\WebService\NodeCreator;
use FKSDB\Models\WebService\XMLHelper;
use Fykosak\NetteORM\Model;
use Fykosak\NetteORM\TypedGroupedSelection;
use Fykosak\Utils\Localization\LocalizedString;
use Nette\DI\Container;
use Nette\InvalidArgumentException;
use Nette\Neon\Neon;
use Nette\Schema\Processor;
use Nette\Security\Resource;

/**
 * @property-read int $event_id
 * @property-read int $event_type_id
 * @property-read EventTypeModel $event_type
 * @property-read int $year
 * @property-read int $event_year
 * @property-read \DateTimeInterface $begin
 * @property-read \DateTimeInterface $end
 * @property-read \DateTimeInterface $registration_begin
 * @property-read \DateTimeInterface $registration_end
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
 *    contestId:int,
 * }
 */
final class EventModel extends Model implements Resource, NodeCreator
{

    private const TEAM_EVENTS = [1, 9, 13, 17];
    public const RESOURCE_ID = 'event';
    private const POSSIBLY_ATTENDING_STATES = [
        TeamState::PARTICIPATED,
        TeamState::APPROVED,
        TeamState::SPARE,
        TeamState::APPLIED,
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
        $selection = $this->getTeams()->where('state', TeamState::PARTICIPATED);
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
     * @phpstan-return TypedGroupedSelection<PaymentModel>
     */
    public function getPayments(): TypedGroupedSelection
    {
        /** @phpstan-var TypedGroupedSelection<PaymentModel> $selection */
        $selection = $this->related(DbNames::TAB_PAYMENT, 'event_id');
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
        return [
            'eventId' => $this->event_id,
            'year' => $this->year,
            'eventYear' => $this->event_year,
            'begin' => $this->begin->format('c'),
            'end' => $this->end->format('c'),
            'registrationBegin' => $this->registration_begin->format('c'),
            'registrationEnd' => $this->registration_end->format('c'),
            'report' => $this->report_cs,
            'reportNew' => $this->report->__serialize(),
            'description' => $this->description->__serialize(),
            'name' => $this->name,
            'nameNew' => $this->getName()->__serialize(),
            'eventTypeId' => $this->event_type_id,
            'contestId' => $this->event_type->contest_id,
        ];
    }

    /**
     * @throws \DOMException
     */
    public function createXMLNode(\DOMDocument $document): \DOMElement
    {
        $node = $document->createElement('event');
        $node->setAttribute('eventId', (string)$this->event_id);
        XMLHelper::fillArrayToNode($this->__toArray(), $document, $node);
        return $node;
    }

    public function isRegistrationOpened(): bool
    {
        return ($this->registration_begin->getTimestamp() <= time())
            && ($this->registration_end->getTimestamp() >= time());
    }

    public function createGameHandler(Container $container): Handler
    {
        switch ($this->event_type_id) {
            case 1:
                return new FOFHandler($this, $container);
            case 17:
                return new CtyrbojHandler($this, $container);
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
}
