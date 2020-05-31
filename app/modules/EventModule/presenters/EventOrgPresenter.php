<?php

namespace EventModule;

use FKSDB\Components\Grids\EventOrgsGrid;
use FKSDB\Exceptions\NotImplementedException;
use FKSDB\ORM\Services\ServiceEventOrg;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Control;

/**
 * Class EventOrgPresenter
 * *
 */
class EventOrgPresenter extends BasePresenter {
    use EventEntityTrait;

    /**
     * @var ServiceEventOrg
     */
    private $serviceEventOrg;

    public function injectServiceEventOrg(ServiceEventOrg $serviceEventOrg): void {
        $this->serviceEventOrg = $serviceEventOrg;
    }

    /**
     * @return void
     * @throws BadRequestException
     */
    public function titleList() {
        $this->setTitle(sprintf(_('Organisers of event')), 'fa fa-users');
    }

    /**
     * @inheritDoc
     * @throws BadRequestException
     */
    protected function traitIsAuthorized($resource, string $privilege): bool {
        return $this->isContestsOrgAuthorized($resource, $privilege);
    }

    /**
     * @param int $id
     * @throws AbortException
     */
    public function actionDelete(int $id) {
        try {
            [$message] = $this->traitHandleDelete($id);
            $this->flashMessage($message->getMessage(), $message->getLevel());
            $this->redirect('list');
        } catch (BadRequestException $exception) {
            $this->flashMessage(_('Error during deleting'), self::FLASH_ERROR);
            $this->redirect('list');
        }
    }

    /**
     * @inheritDoc
     */
    protected function getORMService(): ServiceEventOrg {
        return $this->serviceEventOrg;
    }

    /**
     * @return EventOrgsGrid
     * @throws AbortException
     * @throws BadRequestException
     */
    protected function createComponentGrid(): EventOrgsGrid {
        return new EventOrgsGrid($this->getEvent(), $this->getContext());
    }

    /**
     * @inheritDoc
     */
    public function createComponentCreateForm(): Control {
        throw new NotImplementedException();
    }

    /**
     * @inheritDoc
     */
    public function createComponentEditForm(): Control {
        throw new NotImplementedException();
    }

}
