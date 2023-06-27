<?php

declare(strict_types=1);

namespace FKSDB\Models\Events\Semantics;

use FKSDB\Models\Events\Model\Holder\BaseHolder;
use FKSDB\Models\ORM\Services\EventParticipantService;
use FKSDB\Models\Transitions\Statement;
use Nette\SmartObject;

class Count implements Statement
{
    use SmartObject;

    private array $states;
    private EventParticipantService $eventParticipantService;

    public function __construct(array $states, EventParticipantService $eventParticipantService)
    {
        $this->states = $states;
        $this->eventParticipantService = $eventParticipantService;
    }

    public function __invoke(...$args): int
    {
        /** @var BaseHolder $holder */
        [$holder] = $args;
        $table = $this->eventParticipantService->getTable();
        $table->where('event_participant.event_id', $holder->event->getPrimary());
        $table->where('status', $this->states);
        return $table->count('1');
    }
}
