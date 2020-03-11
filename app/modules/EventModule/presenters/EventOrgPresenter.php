<?php

namespace EventModule;

use FKSDB\Components\Grids\EventOrgsGrid;
use FKSDB\ORM\Services\ServiceEventOrg;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;

/**
 * Class EventOrgPresenter
 * @package EventModule
 */
class EventOrgPresenter extends BasePresenter {
    /**
     * @var ServiceEventOrg
     */
    private $serviceEventOrg;

    /**
     * @param ServiceEventOrg $serviceEventOrg
     */
    public function injectServiceEventOrg(ServiceEventOrg $serviceEventOrg) {
        $this->serviceEventOrg = $serviceEventOrg;
    }

    /**
     * @throws AbortException
     * @throws BadRequestException
     */
    public function titleList() {
        $this->setTitle(sprintf(_('Organizátoři akce %s'), $this->getEvent()->name));
        $this->setIcon('fa fa-users');
    }

    /**
     * @return EventOrgsGrid
     * @throws AbortException
     * @throws BadRequestException
     */
    protected function createComponentGrid(): EventOrgsGrid {
        return new EventOrgsGrid($this->getEvent(), $this->serviceEventOrg, $this->getTableReflectionFactory());
    }
}
