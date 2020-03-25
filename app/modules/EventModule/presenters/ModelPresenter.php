<?php

namespace EventModule;

use Events\Machine\Machine;
use FKSDB\Components\Events\ExpressionPrinter;
use FKSDB\Components\Events\GraphComponent;
use Nette\Application\BadRequestException;

/**
 * Class ModelPresenter
 * @package EventModule
 */
class ModelPresenter extends BasePresenter {

    /**
     * @var ExpressionPrinter
     */
    private $expressionPrinter;

    /**
     * @param ExpressionPrinter $expressionPrinter
     */
    public function injectExpressionPrinter(ExpressionPrinter $expressionPrinter) {
        $this->expressionPrinter = $expressionPrinter;
    }

    /**
     * @throws BadRequestException
     */
    public function authorizedDefault() {
        $this->setAuthorized($this->getContestAuthorizator()->isAllowed('event.model', 'default', $this->getEvent()->getContest()));

    }

    public function titleDefault() {
        $this->setTitle(_('Model akce'));
        $this->setIcon('fa fa-cubes');
    }

    /**
     * @return GraphComponent
     * @throws BadRequestException
     */
    protected function createComponentGraphComponent(): GraphComponent {
        $event = $this->getEvent();
        /**
         * @var Machine $machine
         */
        $machine = $this->container->createEventMachine($event);

        return new GraphComponent($machine->getPrimaryMachine(), $this->expressionPrinter);
    }
}
