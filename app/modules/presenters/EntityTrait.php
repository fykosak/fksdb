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
     * @var int
     * @persistent
     */
    public $id;
    /**
     * @var AbstractModelSingle|IModel
     */
    private $model;

    /**
     * @throws BadRequestException
     */
    public function authorizedDetail() {
        $this->setAuthorized($this->isAllowed($this->getEntity(), 'detail'));
    }

    public function authorizedList() {
        $this->setAuthorized($this->isAllowed($this->getModelResource(), 'list'));
    }

    /**
     * @throws BadRequestException
     */
    public function authorizedEdit() {
        $this->setAuthorized($this->isAllowed($this->getEntity(), 'edit'));
    }

    public function authorizedCreate() {
        $this->setAuthorized($this->isAllowed($this->getModelResource(), 'create'));
    }

    /**
     * @return AbstractModelSingle|IModel
     * @throws BadRequestException
     * @deprecated
     */
    public function getModel() {
        return $this->getEntity();
    }

    /**
     * @return AbstractModelSingle|IModel
     * @throws BadRequestException
     */
    public function getEntity() {

        // protection for tests ev. change URL during app is running
        if ($this->model && $this->id !== $this->model->getPrimary()) {
          //  $this->model = null;
        }
        if (!$this->model) {
            $model = $this->getORMService()->findByPrimary($this->id);
            Debugger::barDump($this->id);
            Debugger::barDump($model);
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
