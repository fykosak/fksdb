<?php

namespace EventModule;

use FKSDB\Components\Events\GraphComponent;
use FKSDB\Events\EventDispatchFactory;
use Nette\Application\BadRequestException;

/**
 * Class ModelPresenter
 * @author Michal Červeňák <miso@fykos.cz>
 */
class ModelPresenter extends BasePresenter {

    /**
     * @return void
     * @throws BadRequestException
     */
    public function authorizedDefault(): void {
        $this->setAuthorized($this->isContestsOrgAuthorized('event.model', 'default'));
    }

    /**
     * @return void
     * @throws BadRequestException
     */
    public function titleDefault(): void {
        $this->setTitle(_('Model of event'), 'fa fa-cubes');
    }

    /**
     * @return GraphComponent
     * @throws BadRequestException
     */
    protected function createComponentGraphComponent(): GraphComponent {
        $machine = $this->getEventDispatchFactory()->getEventMachine($this->getEvent());
        return new GraphComponent($this->getContext(), $machine->getPrimaryMachine());
    }
}
