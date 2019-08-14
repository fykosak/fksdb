<?php

namespace FKSDB;

use FKSDB\ORM\IModel;
use FKSDB\ORM\IService;
use Nette\Application\BadRequestException;

/**
 * Trait EntityTrait
 */
trait EntityTrait {
    /**
     * @var int
     * @persistent
     */
    public $id;
    /**
     * @var
     */
    private $model;

    /**
     * @throws BadRequestException
     */
    public function authorizedDetail() {
        $this->setAuthorized($this->isAllowed($this->getModel(), 'detail'));
    }

    public function authorizedList() {
        $this->setAuthorized($this->isAllowed($this->getModelResource(), 'list'));
    }

    /**
     * @throws BadRequestException
     */
    public function authorizedEdit() {
        $this->setAuthorized($this->isAllowed($this->getModel(), 'edit'));
    }

    public function authorizedCreate() {
        $this->setAuthorized($this->isAllowed($this->getModelResource(), 'create'));
    }

    /**
     * @return \FKSDB\ORM\AbstractModelSingle|IModel
     * @throws BadRequestException
     */
    public function getModel() {
        if (!$this->model) {
            $model = $this->getORMService()->findByPrimary($this->id);
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
