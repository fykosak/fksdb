<?php

declare(strict_types=1);

namespace FKSDB\Modules\EventModule;

use FKSDB\Components\Charts\Event\Model\GraphComponent;
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
        $this->setAuthorized($this->isAllowed('event.model', 'default'));
    }

    public function titleDefault(): PageTitle
    {
        return new PageTitle(null, _('Model of event'), 'fa fa-project-diagram');
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
