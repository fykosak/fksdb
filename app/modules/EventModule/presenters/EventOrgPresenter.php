<?php

namespace EventModule;

use FKSDB\Components\Grids\EventOrgsGrid;
use FKSDB\EntityTrait;
use FKSDB\Messages\Message;
use FKSDB\ORM\IService;
use FKSDB\ORM\Models\ModelEventOrg;
use FKSDB\ORM\Services\ServiceEventOrg;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;

/**
 * Class EventOrgPresenter
 * @package EventModule
 */
class EventOrgPresenter extends BasePresenter {
    use EventEntityTrait;
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

    /*
     * @param int $id
     *
    public function handleDelete(int $id) {
        try {
            list($message) = $this->traitHandleDelete($id);
            $this->flashMessage($message->getMessage(), $message->getLevel());
        } catch (\Exception $exception) {
            $this->flashMessage(_('Error during deleting'), self::FLASH_ERROR);
        }
    }*/

    /**
     * @inheritDoc
     */
    protected function getORMService(): ServiceEventOrg {
        return $this->serviceEventOrg;
    }

    /**
     * @inheritDoc
     */
    protected function getModelResource(): string {
        return ModelEventOrg::RESOURCE_ID;
    }
}
