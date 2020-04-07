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
        $this->setAuthorized($this->isContestsOrgAuthorized('event.model', 'default'));

    }

    public function titleDefault() {
        $this->setTitle(_('Model of event'), 'fa fa-cubes');
    }

    /**
     * @return GraphComponent
     * @throws BadRequestException
     */
    protected function createComponentGraphComponent(): GraphComponent {
        $event = $this->getEvent();
        /** @var Machine $machine */
        $machine = $this->getContext()->createEventMachine($event);

        return new GraphComponent($machine->getPrimaryMachine(), $this->expressionPrinter);
    }
}
