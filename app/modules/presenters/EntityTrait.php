<?php

namespace FKSDB;

use FKSDB\Components\Controls\Entity\IEditEntityForm;
use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Components\Grids\BaseGrid;
use FKSDB\Exceptions\BadTypeException;
use FKSDB\Exceptions\NotImplementedException;
use FKSDB\Messages\Message;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\AbstractServiceSingle;
use FKSDB\ORM\IModel;
use FKSDB\ORM\IService;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Control;
use Nette\InvalidStateException;
use Nette\Security\IResource;

/**
 * Trait EntityTrait
 * @author Michal Červeňák <miso@fykos.cz>
 */
trait EntityTrait {

    private ?AbstractModelSingle $model;

    public function authorizedList(): void {
        $this->setAuthorized($this->traitIsAuthorized($this->getModelResource(), 'list'));
    }

    public function authorizedCreate(): void {
        $this->setAuthorized($this->traitIsAuthorized($this->getModelResource(), 'create'));
    }

    /**
     * @param int $id
     * @throws BadRequestException
     */
    public function authorizedEdit(int $id): void {
        $this->setAuthorized($this->traitIsAuthorized($this->loadEntity($id), 'edit'));
    }

    /**
     * @param int $id
     * @throws BadRequestException
     */
    public function authorizedDelete(int $id): void {
        $this->setAuthorized($this->traitIsAuthorized($this->loadEntity($id), 'delete'));
    }

    /**
     * @param int $id
     * @throws BadRequestException
     */
    public function authorizedDetail(int $id): void {
        $this->setAuthorized($this->traitIsAuthorized($this->loadEntity($id), 'detail'));
    }


    public function titleList(): void {
    }

    public function titleCreate(): void {
    }

    public function titleEdit(int $id): void {
    }

    public function titleDetail(int $id): void {
    }

    public function titleDelete(int $id): void {
    }

    /**
     * @return AbstractModelSingle|IModel
     * @throws InvalidStateException
     */
    public function getEntity(): AbstractModelSingle {
        if (!isset($this->model)) {
            throw new InvalidStateException(_('Entity is not loaded'));
        }
        return $this->model;
    }

    /**
     * @param int $id
     * @return AbstractModelSingle|IModel
     * @throws BadRequestException
     */
    public function loadEntity(int $id): AbstractModelSingle {
        // protection for tests ev. change URL during app is running
        if (isset($this->model) && $id !== $this->model->getPrimary()) {
            $this->model = null;
        }
        if (!isset($this->model) || is_null($this->model)) {
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
    protected function traitActionEdit(int $id): void {
        $component = $this->getComponent('editForm');
        if (!$component instanceof IEditEntityForm) {
            throw new BadTypeException(IEditEntityForm::class, $component);
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
            throw new Exceptions\ModelException(_('Error during deleting'));
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

    abstract protected function getORMService(): AbstractModelSingle;

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
