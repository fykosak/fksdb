<?php

namespace FKSDB;

use FKSDB\Components\Controls\Entity\IEditEntityForm;
use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Components\Grids\BaseGrid;
use FKSDB\Messages\Message;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\AbstractServiceSingle;
use FKSDB\ORM\IModel;
use FKSDB\ORM\IService;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use Nette\Application\ForbiddenRequestException;
use Nette\Application\UI\Control;
use Nette\Security\IResource;

/**
 * Trait EntityTrait
 */
trait EntityTrait {
    /**
     * @var AbstractModelSingle|IModel
     */
    private $model;

    public function authorizedList() {
        $this->setAuthorized($this->traitIsAuthorized($this->getModelResource(), 'list'));
    }

    public function authorizedCreate() {
        $this->setAuthorized($this->traitIsAuthorized($this->getModelResource(), 'create'));
    }

    /**
     * @param int $id
     * @throws BadRequestException
     */
    public function authorizedEdit(int $id) {
        $this->setAuthorized($this->traitIsAuthorized($this->loadEntity($id), 'edit'));
    }

    /**
     * @param int $id
     * @throws BadRequestException
     */
    public function authorizedDelete(int $id) {
        $this->setAuthorized($this->traitIsAuthorized($this->loadEntity($id), 'delete'));
    }

    /**
     * @param int $id
     * @throws BadRequestException
     */
    public function authorizedDetail(int $id) {
        $this->setAuthorized($this->traitIsAuthorized($this->loadEntity($id), 'detail'));
    }


    public function titleList() {
    }

    public function titleCreate() {
    }

    /**
     * @param int $id
     */
    public function titleEdit(int $id) {
    }

    /**
     * @param int $id
     */
    public function titleDetail(int $id) {
    }

    /**
     * @param int $id
     */
    public function titleDelete(int $id) {
    }

    /**
     * @return AbstractModelSingle|IModel
     */
    public function getEntity() {
        return $this->model;
    }

    /**
     * @param int $id
     * @return AbstractModelSingle|IModel
     * @throws BadRequestException
     */
    public function loadEntity(int $id) {
        // protection for tests ev. change URL during app is running
        if ($this->model && $id !== $this->model->getPrimary()) {
            $this->model = null;
        }
        if (!$this->model) {
            $model = $this->getORMService()->findByPrimary($id);
            if (!$model) {
                throw new BadRequestException('Model neexistuje');
            }
            $this->model = $model;
        }
        return $this->model;
    }


    /**
     * @param int $id
     * @throws BadRequestException
     */
    protected function traitActionEdit(int $id) {
        $component = $this->getComponent('editForm');
        if (!$component instanceof IEditEntityForm) {
            throw new BadRequestException();
        }
        $component->setModel($this->loadEntity($id));
    }

    /**
     * @param int $id
     * @return array
     * @throws BadRequestException
     */
    public function traitHandleDelete(int $id) {
        $success = $this->loadEntity($id)->delete();
        if (!$success) {
            throw new \ModelException(_('Error during deleting'));
        }
        return [new Message(_('Entity has been deleted'), self::FLASH_SUCCESS)];
    }

    /**
     * @return FormControl
     * @throws BadRequestException
     * @throws NotImplementedException
     */
    abstract public function createComponentCreateForm(): Control;

    /**
     * @return FormControl
     * @throws BadRequestException
     * @throws NotImplementedException
     */
    abstract public function createComponentEditForm(): Control;

    /**
     * @throws NotImplementedException
     */
    abstract protected function createComponentGrid(): BaseGrid;

    /**
     * @return IService|AbstractServiceSingle
     */
    abstract protected function getORMService();

    /**
     * @return string
     */
    protected function getModelResource(): string {
        return $this->getORMService()->getModelClassName()::RESOURCE_ID;
    }

    /**
     * @param IResource|string $resource
     * @param string $privilege
     * @return bool
     */
    abstract protected function traitIsAuthorized($resource, string $privilege): bool;
}
