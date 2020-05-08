<?php

namespace FKSDB\ORM;

use LogicException;
use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\Selection;
use Nette\InvalidStateException;
use Nette\SmartObject;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
abstract class AbstractModelMulti implements IModel {
    use SmartObject;

    /**
     * @var AbstractModelSingle
     */
    protected $mainModel;

    /**
     * @var AbstractModelSingle
     */
    protected $joinedModel;

    /**
     * @var AbstractServiceMulti
     */
    protected $service;

    /**
     * @note DO NOT use directly, use AbstracServiceMulti::composeModel or FKSDB\ORM\AbstractModelMulti::createFromExistingModels.
     *
     * @param AbstractServiceMulti $service
     * @param AbstractModelSingle $mainModel
     * @param AbstractModelSingle $joinedModel
     */
    public function __construct($service, AbstractModelSingle $mainModel, AbstractModelSingle $joinedModel) {
        if ($service == null) {
            $this->joinedModel = $joinedModel;
            $this->mainModel = $mainModel;
        } else {
            $this->service = $service;
            $this->setJoinedModel($joinedModel);
            $this->setMainModel($mainModel);
        }
    }

    /**
     * @param $mainModel
     * @param $joinedModel
     * @return AbstractModelMulti
     */
    public static function createFromExistingModels($mainModel, $joinedModel) {
        return new static(null, $mainModel, $joinedModel);
    }

    /**
     * @return array|mixed
     */
    public function toArray() {
        return $this->getMainModel()->toArray() + $this->getJoinedModel()->toArray();
    }

    /**
     * @return AbstractModelSingle|IModel
     */
    public function getMainModel() {
        return $this->mainModel;
    }

    /**
     * @param AbstractModelSingle $mainModel
     */
    public function setMainModel(AbstractModelSingle $mainModel) {
        if (!$this->service) {
            throw new InvalidStateException('Cannot set main model on multimodel w/out service.');
        }
        $this->mainModel = $mainModel;
        if (!$mainModel->isNew() && $this->getJoinedModel()) { // bind via foreign key
            $joiningColumn = $this->service->getJoiningColumn();
            $this->getJoinedModel()->{$joiningColumn} = $mainModel->getPrimary();
        }
    }

    /**
     * @return AbstractModelSingle|IModel
     */
    public function getJoinedModel() {
        return $this->joinedModel;
    }

    /**
     * @param AbstractModelSingle $joinedModel
     */
    public function setJoinedModel(AbstractModelSingle $joinedModel) {
        $this->joinedModel = $joinedModel;
    }

    /**
     * @return AbstractServiceMulti
     */
    public function getService() {
        return $this->service;
    }

    /**
     * @param AbstractServiceMulti $service
     */
    public function setService(AbstractServiceMulti $service) {
        $this->service = $service;
    }

    /**
     * @param $name
     * @return bool|mixed|ActiveRow|Selection|null
     */
    public function &__get($name) {
        if ($this->getMainModel()->__isset($name)) {
            return $this->getMainModel()->__get($name);
        }
        if ($this->getJoinedModel()->__isset($name)) {
            return $this->getJoinedModel()->__get($name);
        }
        // this reference isn't that important
        $null = null;
        return $null;
    }

    /**
     * @param $name
     * @return bool
     */
    public function __isset($name) {
        return $this->getMainModel()->__isset($name) || $this->getJoinedModel()->__isset($name);
    }

    /**
     * @param $name
     * @param $value
     */
    public function __set($name, $value) {
        throw new LogicException("Cannot update multimodel directly.");
    }

    /**
     * @param $name
     */
    public function __unset($name) {
        throw new LogicException("Cannot update multimodel directly.");
    }

    /**
     * @param bool $need
     * @return mixed
     */
    public function getPrimary($need = TRUE) {
        return $this->getJoinedModel()->getPrimary($need);
    }

    /**
     * @param bool $need
     * @return string
     */
    public function getSignature($need = TRUE) {
        return implode('|', (array)$this->getPrimary($need));
    }

    /**
     * @return bool|mixed
     */
    public function isNew() {
        return $this->getJoinedModel()->isNew();
    }

    /**
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset) {
        return $this->__isset($offset);
    }

    /**
     * @param mixed $offset
     * @return bool|mixed|ActiveRow|Selection|null
     */
    public function &offsetGet($offset) {
        return $this->__get($offset);
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value) {
        throw new LogicException("Cannot update multimodel directly.");
    }

    /**
     * @param mixed $offset
     */
    public function offsetUnset($offset) {
        throw new LogicException("Cannot update multimodel directly.");
    }

}
