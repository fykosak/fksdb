<?php

namespace FKSDB;

use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\IModel;
use FKSDB\ORM\IService;
use Nette\Application\BadRequestException;

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
    public function authorizedDetail(int $id): void {
        $this->setAuthorized($this->isAllowed($this->loadEntity($id), 'detail'));
    }

    public function authorizedList(): void {
        $this->setAuthorized($this->isAllowed($this->getModelResource(), 'list'));
    }

    /**
     * @param int $id
     * @throws BadRequestException
     */
    public function authorizedEdit(int $id): void {
        $this->setAuthorized($this->isAllowed($this->loadEntity($id), 'edit'));
    }

    public function authorizedCreate(): void {
        $this->setAuthorized($this->isAllowed($this->getModelResource(), 'create'));
    }

    /**
     * @return AbstractModelSingle|IModel
     */
    public function getEntity(): AbstractModelSingle {
        return $this->model;
    }

    /**
     * @param int $id
     * @return AbstractModelSingle|IModel
     * @throws BadRequestException
     */
    public function loadEntity(int $id): AbstractModelSingle {
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
     * @return IService
     */
    abstract protected function getORMService();

    /**
     * @return string
     */
    abstract protected function getModelResource(): string;

    /**
     * @param $resource
     * @param $privilege
     * @return bool
     */
    abstract protected function isAllowed($resource, $privilege): bool;
}
