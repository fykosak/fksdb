<?php


namespace EventModule;

use Events\Machine\Machine;
use FKSDB\Components\Events\ExpressionPrinter;
use FKSDB\Components\Events\GraphComponent;

class ModelPresenter extends BasePresenter {

    /**
     * @var ExpressionPrinter
     */
    private $expressionPrinter;

    public function injectExpressionPrinter(ExpressionPrinter $expressionPrinter) {
        $this->expressionPrinter = $expressionPrinter;
    }

    public function authorizedDefault() {
        $this->setAuthorized($this->eventIsAllowed('event.model', 'default'));
    }

    public function titleDefault() {
        $this->setTitle(_('Model akce'));
        $this->setIcon('fa fa-cubes');
    }

    protected function createComponentGraphComponent(): GraphComponent {
        $event = $this->getEvent();
        /**
         * @var Machine $machine
         */
        $machine = $this->container->createEventMachine($event);

        return new GraphComponent($machine->getPrimaryMachine(), $this->expressionPrinter);
    }
}
