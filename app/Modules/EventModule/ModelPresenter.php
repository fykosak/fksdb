<?php

namespace FKSDB\Modules\EventModule;

use FKSDB\Components\Controls\Events\GraphComponent;
use FKSDB\Models\Events\Exceptions\ConfigurationNotFoundException;
use FKSDB\Models\Events\Exceptions\EventNotFoundException;
use FKSDB\Models\UI\PageTitle;

/**
 * Class ModelPresenter
 * @author Michal Červeňák <miso@fykos.cz>
 */
class ModelPresenter extends BasePresenter {

    /**
     * @return void
     * @throws EventNotFoundException
     */
    public function authorizedDefault(): void {
        $this->setAuthorized($this->isContestsOrgAuthorized('event.model', 'default'));
    }

    /**
     * @return void
     * @throws EventNotFoundException
     */
    public function titleDefault(): void {
        $this->setPageTitle(new PageTitle(_('Model of event'), 'fa fa-cubes'));
    }

    /**
     * @return GraphComponent
     * @throws EventNotFoundException
     * @throws ConfigurationNotFoundException
     */
    protected function createComponentGraphComponent(): GraphComponent {
        $machine = $this->eventDispatchFactory->getEventMachine($this->getEvent());
        return new GraphComponent($this->getContext(), $machine->getPrimaryMachine());
    }
}
