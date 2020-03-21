<?php

namespace EventModule;

use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Components\Grids\EventOrgsGrid;
use FKSDB\NotImplementedException;
use FKSDB\ORM\Models\ModelEventOrg;
use FKSDB\ORM\Services\ServiceEventOrg;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;

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
     * @param int $id
     * @throws AbortException
     */
    public function actionDelete(int $id) {
        try {
            list($message) = $this->traitHandleDelete($id);
            $this->flashMessage($message->getMessage(), $message->getLevel());
            $this->redirect('list');
        } catch (BadRequestException $exception) {
            $this->flashMessage(_('Error during deleting'), self::FLASH_ERROR);
            $this->redirect('list');
        }
    }

    /**
     * @return EventOrgsGrid
     * @throws AbortException
     * @throws BadRequestException
     */
    protected function createComponentGrid(): EventOrgsGrid {
        return new EventOrgsGrid($this->getEvent(), $this->serviceEventOrg, $this->getTableReflectionFactory());
    }


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

    /**
     * @inheritDoc
     */
    protected function getCreateForm(): FormControl {
        throw new NotImplementedException();
    }

    /**
     * @inheritDoc
     */
    protected function getEditForm(): FormControl {
        throw new NotImplementedException();
    }

    /**
     * @inheritDoc
     */
    protected function handleCreateFormSuccess(Form $form) {
        throw new NotImplementedException();
    }

    /**
     * @inheritDoc
     */
    protected function handleEditFormSuccess(Form $form) {
        throw new NotImplementedException();
    }
}
