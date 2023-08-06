<?php

declare(strict_types=1);

namespace FKSDB\Models\Events\Semantics;

use FKSDB\Models\Events\Model\Holder\BaseHolder;
use FKSDB\Models\ORM\Services\EventParticipantService;
use FKSDB\Models\Transitions\Statement;
use Nette\SmartObject;

/**
 * @implements Statement<int,BaseHolder>
 */
class Count implements Statement
{
    use SmartObject;

    /** @var string[] */
    private array $states;
    private EventParticipantService $eventParticipantService;

    /**
     * @param string[] $states
     */
    public function __construct(array $states, EventParticipantService $eventParticipantService)
    {
        $this->states = $states;
        $this->eventParticipantService = $eventParticipantService;
    }

    /**
     * @param BaseHolder ...$args
     */
    public function __invoke(...$args): int
    {
        [$holder] = $args;
        $table = $this->eventParticipantService->getTable();
        $table->where('event_participant.event_id', $holder->event->getPrimary());
        $table->where('status', $this->states);
        return $table->count('1');
    }
}
