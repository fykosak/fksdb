<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models;

use FKSDB\Components\Game\NotSetGameParametersException;
use FKSDB\Components\Game\Submits\Handler\CtyrbojHandler;
use FKSDB\Components\Game\Submits\Handler\FOFHandler;
use FKSDB\Components\Game\Submits\Handler\Handler;
use FKSDB\Models\ORM\DbNames;
use FKSDB\Models\ORM\Models\Fyziklani\GameSetupModel;
use FKSDB\Models\ORM\Models\Fyziklani\TeamState;
use FKSDB\Models\WebService\NodeCreator;
use FKSDB\Models\WebService\XMLHelper;
use Fykosak\NetteORM\Model;
use Fykosak\NetteORM\TypedGroupedSelection;
use Nette\DI\Container;
use Nette\InvalidArgumentException;
use Nette\Neon\Neon;
use Nette\Schema\Processor;
use Nette\Security\Resource;

/**
 * @property-read int event_year
 * @property-read int year
 * @property-read string name
 * @property-read int event_id
 * @property-read string report
 * @property-read EventTypeModel event_type
 * @property-read int event_type_id
 * @property-read \DateTimeInterface begin
 * @property-read \DateTimeInterface end
 * @property-read \DateTimeInterface|null registration_begin
 * @property-read \DateTimeInterface|null registration_end
 * @property-read string parameters
 */
class EventModel extends Model implements Resource, NodeCreator
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

    public function getResourceId(): string
    {
        return self::RESOURCE_ID;
    }

    public function __toString(): string
    {
        return $this->name;
    }

    public function isTeamEvent(): bool
    {
        return in_array($this->event_type_id, EventModel::TEAM_EVENTS);
    }

    /**
     * @throws NotSetGameParametersException
     */
    public function getGameSetup(): GameSetupModel
    {
        $gameSetupRow = $this->related(DbNames::TAB_FYZIKLANI_GAME_SETUP, 'event_id')->fetch();
        if (!$gameSetupRow) {
            throw new NotSetGameParametersException();
        }
        return $gameSetupRow;
    }

    public function getScheduleGroups(): TypedGroupedSelection
    {
        return $this->related(DbNames::TAB_SCHEDULE_GROUP, 'event_id');
    }

    public function getParticipants(): TypedGroupedSelection
    {
        return $this->related(DbNames::TAB_EVENT_PARTICIPANT, 'event_id');
    }

    public function getPossiblyAttendingParticipants(): TypedGroupedSelection
    {
        return $this->getParticipants()->where('status', self::POSSIBLY_ATTENDING_STATES);
    }

    public function getTeams(): TypedGroupedSelection
    {
        return $this->related(DbNames::TAB_FYZIKLANI_TEAM, 'event_id');
    }

    public function getParticipatingTeams(): TypedGroupedSelection
    {
        return $this->getTeams()->where('state', TeamState::PARTICIPATED);
    }

    public function getPossiblyAttendingTeams(): TypedGroupedSelection
    {
        // TODO
        return $this->getTeams()->where('state', self::POSSIBLY_ATTENDING_STATES);
    }

    public function getEventOrgs(): TypedGroupedSelection
    {
        return $this->related(DbNames::TAB_EVENT_ORG, 'event_id');
    }

    public function getPayments(): TypedGroupedSelection
    {
        return $this->related(DbNames::TAB_PAYMENT, 'event_id');
    }

    public function getTasks(): TypedGroupedSelection
    {
        return $this->related(DbNames::TAB_FYZIKLANI_TASK, 'event_id');
    }

    public function __toArray(): array
    {
        return [
            'eventId' => $this->event_id,
            'year' => $this->year,
            'eventYear' => $this->event_year,
            'begin' => $this->begin ? $this->begin->format('c') : null,
            'end' => $this->end ? $this->end->format('c') : null,
            'registrationBegin' => $this->registration_begin ? $this->registration_begin->format('c') : null,
            'registrationEnd' => $this->registration_end ? $this->registration_end->format('c') : null,
            'report' => $this->report,
            'name' => $this->name,
            'eventTypeId' => $this->event_type_id,
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
        return ($this->registration_begin && $this->registration_begin->getTimestamp() <= time())
            && ($this->registration_end && $this->registration_end->getTimestamp() >= time());
    }

    public function createGameHandler(Container $container): Handler
    {
        switch ($this->event_type_id) {
            case 1:
                return new FOFHandler($this, $container);
            case 17:
                return new CtyrbojHandler($this, $container);
        }
    }

    public function getPaymentFactoryName(): ?string
    {
        if ($this->event_type_id === 1) {
            return sprintf('fyziklani%dpayment', $this->event_year);
        }
        return null;
    }

    private function getParameters(): array
    {
        $parameters = $this->parameters ? Neon::decode($this->parameters) : [];
        $processor = new Processor();
        return $processor->process($this->event_type->getParamSchema(), $parameters);
    }

    /**
     * @return mixed
     */
    public function getParameter(string $name)
    {
        try {
            return $this->getParameters()[$name] ?? null;
        } catch (InvalidArgumentException $exception) {
            throw new InvalidArgumentException("No parameter '$name' for event " . $this->name . '.', 0, $exception);
        }
    }
}
