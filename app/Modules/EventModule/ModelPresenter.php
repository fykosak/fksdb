<?php

declare(strict_types=1);

namespace FKSDB\Modules\EventModule;

use FKSDB\Components\Controls\Events\GraphComponent;
use FKSDB\Models\Events\Exceptions\ConfigurationNotFoundException;
use FKSDB\Models\Events\Exceptions\EventNotFoundException;
use Fykosak\Utils\UI\PageTitle;

class ModelPresenter extends BasePresenter
{

    /**
     * @throws EventNotFoundException
     */
    public function authorizedDefault(): void
    {
        $this->setAuthorized($this->isContestsOrgAuthorized('event.model', 'default'));
    }

    public function titleDefault(): PageTitle
    {
        return new PageTitle(_('Model of event'), 'fa fa-project-diagram');
    }

    /**
     * @throws EventNotFoundException
     * @throws ConfigurationNotFoundException
     */
    protected function createComponentGraphComponent(): GraphComponent
    {
        $machine = $this->eventDispatchFactory->getEventMachine($this->getEvent());
        return new GraphComponent($this->getContext(), $machine->getPrimaryMachine());
    }
}
