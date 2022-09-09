<?php

declare(strict_types=1);

namespace FKSDB\Components\Charts\Event\Model;

use FKSDB\Components\Charts\Core\Chart;
use FKSDB\Components\Controls\Events\ExpressionPrinter;
use FKSDB\Models\Events\Machine\BaseMachine;
use FKSDB\Models\Events\Machine\Transition;
use FKSDB\Models\ORM\Models\EventParticipantStatus;
use FKSDB\Models\Transitions\Machine\AbstractMachine;
use Fykosak\NetteFrontendComponent\Components\FrontEndComponent;
use Nette\DI\Container;

class GraphComponent extends FrontEndComponent implements Chart
{

    private BaseMachine $baseMachine;
    private ExpressionPrinter $expressionPrinter;

    public function __construct(Container $container, BaseMachine $baseMachine)
    {
        parent::__construct($container, 'event.model.graph');
        $this->baseMachine = $baseMachine;
    }

    final public function injectExpressionPrinter(ExpressionPrinter $expressionPrinter): void
    {
        $this->expressionPrinter = $expressionPrinter;
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
                EventParticipantStatus::tryFrom(AbstractMachine::STATE_INIT),
                EventParticipantStatus::tryFrom(AbstractMachine::STATE_TERMINATED),
            ]
        );
    }

    /**
     * @return array[]
     */
    private function prepareNodes(): array
    {
        $states = $this->getAllStates();
        $nodes = [];
        foreach ($states as $state) {
            $nodes[$state->value] = [
                'label' => $state->value,
                'type' => $state->value === AbstractMachine::STATE_INIT
                    ? 'init'
                    : ($state->value === AbstractMachine::STATE_TERMINATED ? 'terminated'
                        : 'default'),
            ];
        }
        return $nodes;
    }

    /**
     * @return array[]
     */
    private function prepareTransitions(): array
    {
        $states = $this->getAllStates();
        $edges = [];
        /** @var Transition $transition */
        foreach ($this->baseMachine->getTransitions() as $transition) {
            foreach ($states as $state) {
                if ($transition->matchSource(EventParticipantStatus::tryFrom($state))) {
                    $edges[] = [
                        'from' => $state,
                        'to' => $transition->target->value,
                        'condition' => $this->expressionPrinter->printExpression($transition->getCondition()),
                        'label' => $transition->getLabel(),
                    ];
                }
            }
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
