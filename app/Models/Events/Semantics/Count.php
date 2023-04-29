<?php

declare(strict_types=1);

namespace FKSDB\Models\Events\Semantics;

use FKSDB\Models\Events\Model\Holder\BaseHolder;
use FKSDB\Models\ORM\Services\EventParticipantService;
use FKSDB\Models\Transitions\Holder\ModelHolder;
use Nette\SmartObject;

class Count
{
    use SmartObject;

    private array $states;
    private EventParticipantService $eventParticipantService;

    public function __construct(array $states, EventParticipantService $eventParticipantService)
    {
        $this->states = $states;
        $this->eventParticipantService = $eventParticipantService;
    }

    /**
     * @param BaseHolder $holder
     */
    public function __invoke(ModelHolder $holder): int
    {
        $table = $this->eventParticipantService->getTable();
        $table->where('event_participant.event_id', $holder->event->getPrimary());
        $table->where('status', $this->states);
        return $table->count('1');
    }

    public function __toString(): string
    {
        $terms = [];
        foreach ($this->states as $term) {
            $terms[] = $term;
        }
        $result = implode(', ', $terms);
        return "count($result)";
    }
}
