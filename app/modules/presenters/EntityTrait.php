<?php

namespace FKSDB;

use FKSDB\Messages\Message;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\IModel;
use FKSDB\ORM\IService;
use Nette\Application\BadRequestException;
use Nette\Application\ForbiddenRequestException;
use Nette\Security\IResource;

/**
 * Trait EntityTrait
 */
trait EntityTrait {
    /**
     * @var AbstractModelSingle|IModel
     */
    private $model;

    /**
     * @param int $id
     * @throws BadRequestException
     */
    public function authorizedDetail(int $id) {
        $this->setAuthorized($this->isAllowed($this->loadEntity($id), 'detail'));
    }

    public function authorizedList() {
        $this->setAuthorized($this->isAllowed($this->getModelResource(), 'list'));
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

    public function authorizedCreate() {
        $this->setAuthorized($this->isAllowed($this->getModelResource(), 'create'));
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
     * @return array
     * @throws BadRequestException
     */
    public function traitHandleDelete(int $id) {
        $entity = $this->loadEntity($id);
        if (!$this->isAllowed($entity, 'delete')) {
            throw new ForbiddenRequestException();
        }
        $success = $entity->delete();
        if ($success) {
            return [new Message(_('Entity has been deleted'), self::FLASH_SUCCESS)];
        }
        throw new \ModelException(_('Error during deleting'));

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
