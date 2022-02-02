<?php

declare(strict_types=1);

namespace FKSDB\Components\Charts\Event\Model;

use FKSDB\Components\Charts\Core\Chart;
use FKSDB\Components\Controls\Events\ExpressionPrinter;
use FKSDB\Models\Events\Machine\BaseMachine;
use FKSDB\Models\Transitions\Machine\Machine;
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
     * @return string[]
     */
    private function getAllStates(): array
    {
        return array_merge($this->baseMachine->getStates(), [Machine::STATE_INIT, Machine::STATE_TERMINATED]);
    }

    /**
     * @return array[]
     */
    private function prepareNodes(): array
    {
        $states = $this->getAllStates();
        $nodes = [];
        foreach ($states as $state) {
            $nodes[$state] = [
                'label' => $this->baseMachine->getStateName($state),
                'type' => $state === Machine::STATE_INIT
                    ? 'init'
                    : ($state === Machine::STATE_TERMINATED ? 'terminated'
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
        foreach ($this->baseMachine->getTransitions() as $transition) {
            foreach ($states as $state) {
                if ($transition->matches($state)) {
                    $edges[] = [
                        'from' => $state,
                        'to' => $transition->targetState,
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
