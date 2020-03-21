<?php

namespace FKSDB;

use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Components\Grids\BaseGrid;
use FKSDB\Messages\Message;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\IModel;
use FKSDB\ORM\IService;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;
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
        $this->setAuthorized($this->isAllowed($this->getModelResource(), 'list'));
    }

    public function authorizedCreate() {
        $this->setAuthorized($this->isAllowed($this->getModelResource(), 'create'));
    }

    /**
     * @param int $id
     * @throws BadRequestException
     */
    public function authorizedEdit(int $id) {
        $this->setAuthorized($this->isAllowed($this->loadEntity($id), 'edit'));
    }

    /**
     * @param int $id
     * @throws BadRequestException
     */
    public function authorizedDelete(int $id) {
        $this->setAuthorized($this->isAllowed($this->loadEntity($id), 'delete'));
    }

    /**
     * @param int $id
     * @throws BadRequestException
     */
    public function authorizedDetail(int $id) {
        $this->setAuthorized($this->isAllowed($this->loadEntity($id), 'detail'));
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
     * @return FormControl
     * @throws BadRequestException
     * @throws NotImplementedException
     */
    public final function createComponentCreateForm(): FormControl {
        $control = $this->getCreateForm();
        $form = $control->getForm();
        $form->onSuccess[] = function (Form $form) {
            $this->handleCreateFormSuccess($form);
        };
        return $control;
    }

    /**
     * @return FormControl
     * @throws BadRequestException
     * @throws NotImplementedException
     */
    public final function createComponentEditForm(): FormControl {
        $control = $this->getEditForm();
        $form = $control->getForm();
        $form->onSuccess[] = function (Form $form) {
            $this->handleEditFormSuccess($form);
        };
        return $control;
    }

    /**
     * @param int $id
     * @throws BadRequestException
     */
    protected function traitActionEdit(int $id) {
        $component = $this->getComponent('editForm');
        if (!$component instanceof FormControl) {
            throw new BadRequestException();
        }
        $form = $component->getForm();
        $form->setDefaults($this->getFormDefaults($this->loadEntity($id)));
    }

    /**
     * @param AbstractModelSingle $model
     * @return array
     */
    protected function getFormDefaults(AbstractModelSingle $model): array {
        return $model->toArray();
    }

    /**
     * @throws NotImplementedException
     */
    abstract public function createComponentGrid(): BaseGrid;

    /**
     * @throws NotImplementedException
     */
    abstract protected function getCreateForm(): FormControl;

    /**
     * @throws NotImplementedException
     */
    abstract protected function getEditForm(): FormControl;

    /**
     * @param Form $form
     * @throws NotImplementedException
     */
    abstract protected function handleCreateFormSuccess(Form $form);

    /**
     * @param Form $form
     * @throws NotImplementedException
     */
    abstract protected function handleEditFormSuccess(Form $form);

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
     * @return IService
     */
    abstract protected function getORMService();

    /**
     * @return string
     */
    abstract protected function getModelResource(): string;

    /**
     * @param IResource|string $resource
     * @param string $privilege
     * @return bool
     */
    abstract protected function isAllowed($resource, string $privilege): bool;
}
