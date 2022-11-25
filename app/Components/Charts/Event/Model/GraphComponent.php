<?php

declare(strict_types=1);

namespace FKSDB\Components\Charts\Event\Model;

use FKSDB\Components\Charts\Core\Chart;
use FKSDB\Models\ORM\Models\EventParticipantStatus;
use FKSDB\Models\Transitions\Machine\EventParticipantMachine;
use FKSDB\Models\Transitions\Machine\Machine;
use Fykosak\NetteFrontendComponent\Components\FrontEndComponent;
use Nette\DI\Container;

class GraphComponent extends FrontEndComponent implements Chart
{

    private EventParticipantMachine $baseMachine;

    public function __construct(Container $container, EventParticipantMachine $baseMachine)
    {
        parent::__construct($container, 'event.model.graph');
        $this->baseMachine = $baseMachine;
    }

    final public function getData(): array
    {
        return [
            'nodes' => $this->prepareNodes(),
            'links' => $this->prepareTransitions(),
        ];
    }

    /**
     * @return EventParticipantStatus[]
     */
    private function getAllStates(): array
    {
        return array_merge(
            EventParticipantStatus::cases(),
            [
                EventParticipantStatus::tryFrom(Machine::STATE_INIT),
            ]
        );
    }

    /**
     * @return array[]
     */
    private function prepareNodes(): array
    {
        $nodes = [];
        foreach ($this->getAllStates() as $state) {
            $nodes[$state->value] = [
                'label' => $state->value,
                'type' => $state->value === Machine::STATE_INIT ? 'init' : 'default',
            ];
        }
        return $nodes;
    }

    /**
     * @return array[]
     */
    private function prepareTransitions(): array
    {
        $edges = [];
        foreach ($this->baseMachine->getTransitions() as $transition) {
            $edges[] = [
                'from' => $transition->source->value,
                'to' => $transition->target->value,
                'label' => $transition->getLabel(),
            ];
        }
        return $edges;
    }

    public function getTitle(): string
    {
        return _('Model of event');
    }

    public function getDescription(): ?string
    {
        return null;
    }
}
