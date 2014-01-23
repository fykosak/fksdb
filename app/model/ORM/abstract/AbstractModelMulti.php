<?php

use Nette\InvalidStateException;
use Nette\Object;
use ORM\IModel;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
abstract class AbstractModelMulti extends Object implements IModel {

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
     * @note DO NOT use directly, use AbstracServiceMulti::composeModel or AbstractModelMulti::createFromExistingModels.
     * 
     * @param AbstractServiceMulti $service
     * @param IModel $mainModel
     * @param IModel $joinedModel
     */
    public function __construct($service, $mainModel, $joinedModel) {
        if ($service == null) {
            $this->joinedModel = $joinedModel;
            $this->mainModel = $mainModel;
        } else {
            $this->service = $service;
            $this->setJoinedModel($joinedModel);
            $this->setMainModel($mainModel);
        }
    }

    public static function createFromExistingModels($mainModel, $joinedModel) {
        return new static(null, $mainModel, $joinedModel);
    }

    public function toArray() {
        return $this->getMainModel()->toArray() + $this->getJoinedModel()->toArray();
    }

    public function getMainModel() {
        return $this->mainModel;
    }

    public function setMainModel(AbstractModelSingle $mainModel) {
        if (!$this->service) {
            throw new InvalidStateException('Cannot set main model on multimodel w/out service.');
        }
        $this->mainModel = $mainModel;
        if (!$mainModel->isNew() && $this->getJoinedModel()) { // bind via foreign key
            $joiningColumn = $this->service->getJoiningColumn();
            $this->getJoinedModel()->$joiningColumn = $mainModel->getPrimary();
        }
    }

    public function getJoinedModel() {
        return $this->joinedModel;
    }

    public function setJoinedModel(AbstractModelSingle $joinedModel) {
        $this->joinedModel = $joinedModel;
    }

    public function getService() {
        return $this->service;
    }

    public function setService(AbstractServiceMulti $service) {
        $this->service = $service;
    }

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

    public function __isset($name) {
        return $this->getMainModel()->__isset($name) || $this->getJoinedModel()->__isset($name);
    }

    public function __set($name, $value) {
        throw new LogicException("Cannot update multimodel directly.");
    }

    public function __unset($name) {
        throw new LogicException("Cannot update multimodel directly.");
    }

    public function getPrimary($need = TRUE) {
        return $this->getJoinedModel()->getPrimary($need);
    }

    public function getSignature($need = TRUE) {
        return implode('|', (array) $this->getPrimary($need));
    }

    public function isNew() {
        return $this->getJoinedModel()->isNew();
    }

    public function offsetExists($offset) {
        return $this->__isset($offset);
    }

    public function &offsetGet($offset) {
        return $this->__get($offset);
    }

    public function offsetSet($offset, $value) {
        throw new LogicException("Cannot update multimodel directly.");
    }

    public function offsetUnset($offset) {
        throw new LogicException("Cannot update multimodel directly.");
    }

}

?>
