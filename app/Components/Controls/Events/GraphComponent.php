<?php

namespace FKSDB\Components\Controls\Events;

use FKSDB\Components\Controls\BaseComponent;
use FKSDB\Components\Controls\Loaders\JavaScriptCollector;
use FKSDB\Models\Events\Machine\BaseMachine;
use FKSDB\Models\Transitions\Machine\AbstractMachine;
use Nette\DI\Container;

class GraphComponent extends BaseComponent {

    private BaseMachine $baseMachine;
    private ExpressionPrinter $expressionPrinter;
    private bool $attachedJS = false;

    public function __construct(Container $container, BaseMachine $baseMachine) {
        parent::__construct($container);
        $this->monitor(JavaScriptCollector::class, function (JavaScriptCollector $collector) {
            if (!$this->attachedJS) {
                $this->attachedJS = true;
                $collector->registerJSFile('js/graph/raphael.js');
                $collector->registerJSFile('js/graph/dracula_graffle.js');
                $collector->registerJSFile('js/graph/dracula_graph.js');
                $collector->registerJSFile('js/eventModelGraph.js');
            }
        });
        $this->baseMachine = $baseMachine;
    }

    final public function injectExpressionPrinter(ExpressionPrinter $expressionPrinter): void {
        $this->expressionPrinter = $expressionPrinter;
    }

    final public function render(): void {
        $this->template->nodes = json_encode($this->prepareNodes());
        $this->template->edges = json_encode($this->prepareTransitions());
        $this->template->id = $this->getHtmlId();
        $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'layout.graph.latte');
    }

    private function getHtmlId(): string {
        return 'graph-' . $this->getUniqueId();
    }

    /**
     * @return string[]
     */
    private function getAllStates(): array {
        return array_merge($this->baseMachine->getStates(), [AbstractMachine::STATE_INIT, AbstractMachine::STATE_TERMINATED]);
    }

    /**
     * @return array[]
     */
    private function prepareNodes(): array {
        $states = $this->getAllStates();
        $nodes = [];
        foreach ($states as $state) {
            $nodes[] = [
                'id' => $state,
                'label' => $this->baseMachine->getStateName($state),
                'type' => $state === AbstractMachine::STATE_INIT ? 'init' : ($state === AbstractMachine::STATE_TERMINATED ? 'terminated' : 'default'),
            ];
        }
        return $nodes;
    }

    /**
     * @return array[]
     */
    private function prepareTransitions(): array {
        $states = $this->getAllStates();
        $edges = [];
        foreach ($this->baseMachine->getTransitions() as $transition) {
            foreach ($states as $state) {
                if ($transition->matches($state)) {
                    $edges[] = [
                        'source' => $state,
                        'target' => $transition->getTargetState(),
                        'condition' => $this->expressionPrinter->printExpression($transition->getCondition()),
                        'label' => $transition->getLabel(),
                    ];
                }
            }
        }
        return $edges;
    }
}
