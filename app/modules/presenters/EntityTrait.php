<?php

namespace FKSDB;

use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\IModel;
use FKSDB\ORM\IService;
use Nette\Application\BadRequestException;
use Tracy\Debugger;

/**
 * Trait EntityTrait
 */
trait EntityTrait {
    /**
     * @var AbstractModelSingle|IModel
     */
    private $model;

    /**
     */
    public function authorizedDetail() {
        $this->setAuthorized($this->isAllowed($this->getEntity(), 'detail'));
    }

    public function authorizedList() {
        $this->setAuthorized($this->isAllowed($this->getModelResource(), 'list'));
    }

    public function authorizedEdit() {
        $this->setAuthorized($this->isAllowed($this->getEntity(), 'edit'));
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
