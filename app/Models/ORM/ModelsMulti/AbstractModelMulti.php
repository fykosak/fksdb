<?php

namespace FKSDB\Models\ORM\ModelsMulti;

use FKSDB\Models\ORM\IModel;
use FKSDB\Models\ORM\Models\OldAbstractModelSingle;
use FKSDB\Models\ORM\ServicesMulti\AbstractServiceMulti;
use LogicException;
use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\Selection;
use Nette\InvalidStateException;
use Nette\SmartObject;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
abstract class AbstractModelMulti extends ActiveRow implements IModel {

    use SmartObject;

    protected OldAbstractModelSingle $mainModel;
    protected OldAbstractModelSingle $joinedModel;
    protected AbstractServiceMulti $service;

    /**
     * @note DO NOT use directly, use AbstractServiceMulti::composeModel or FKSDB\Models\ORM\AbstractModelMulti::createFromExistingModels.
     *
     * @param AbstractServiceMulti|null $service
     * @param OldAbstractModelSingle $mainModel
     * @param OldAbstractModelSingle $joinedModel
     */
    public function __construct(?AbstractServiceMulti $service, OldAbstractModelSingle $mainModel, OldAbstractModelSingle $joinedModel) {
        parent::__construct($mainModel->toArray(), $mainModel->getTable());
        if (is_null($service)) {
            $this->joinedModel = $joinedModel;
            $this->mainModel = $mainModel;
        } else {
            $this->service = $service;
            $this->setJoinedModel($joinedModel);
            $this->setMainModel($mainModel);
        }
    }

    public static function createFromExistingModels(OldAbstractModelSingle $mainModel, OldAbstractModelSingle $joinedModel): self {
        return new static(null, $mainModel, $joinedModel);
    }

    public function toArray(): array {
        return $this->getMainModel()->toArray() + $this->getJoinedModel()->toArray();
    }

    public function getMainModel(): OldAbstractModelSingle {
        return $this->mainModel;
    }

    public function setMainModel(OldAbstractModelSingle $mainModel): void {
        if (!isset($this->service)) {
            throw new InvalidStateException('Cannot set main model on multiModel w/out service.');
        }
        $this->mainModel = $mainModel;
        if (!$mainModel->isNew() && $this->getJoinedModel()) { // bind via foreign key
            $joiningColumn = $this->service->getJoiningColumn();
            $this->getJoinedModel()->{$joiningColumn} = $mainModel->getPrimary();
        }
    }

    public function getJoinedModel(): OldAbstractModelSingle {
        return $this->joinedModel;
    }

    public function setJoinedModel(OldAbstractModelSingle $joinedModel): void {
        $this->joinedModel = $joinedModel;
    }

    public function getService(): AbstractServiceMulti {
        return $this->service;
    }

    public function setService(AbstractServiceMulti $service): void {
        $this->service = $service;
    }

    /**
     * @param string|int $key
     * @return bool|mixed|ActiveRow|Selection|null
     */
    public function &__get(string $key) {
        // $value = $this->getMainModel()->{$name} ?? $this->getJoinedModel()->{$name} ?? null;
        if ($this->getMainModel()->__isset($key)) {
            return $this->getMainModel()->__get($key);
        }
        if ($this->getJoinedModel()->__isset($key)) {
            return $this->getJoinedModel()->__get($key);
        }
        // this reference isn't that important
        $null = null;
        return $null;
    }

    /**
     * @param string|int $name
     * @return bool
     */
    public function __isset($name): bool {
        return $this->getMainModel()->__isset($name) || $this->getJoinedModel()->__isset($name);
    }

    /**
     * @param string|int $column
     * @param mixed $value
     */
    public function __set($column, $value): void {
        throw new LogicException('Cannot update multiModel directly.');
    }

    /**
     * @param string|int $key
     */
    public function __unset($key) {
        throw new LogicException('Cannot update multiModel directly.');
    }

    /**
     * @param bool $throw
     * @return mixed
     */
    public function getPrimary($throw = true) {
        return $this->getJoinedModel()->getPrimary($throw);
    }

    public function getSignature(bool $throw = true): string {
        return implode('|', (array)$this->getPrimary($throw));
    }

    /**
     * @return bool
     * @deprecated
     */
    public function isNew(): bool {
        return $this->getJoinedModel()->isNew();
    }

    /**
     * @param mixed $column
     * @return bool
     */
    public function offsetExists($column): bool {
        return $this->__isset($column);
    }

    /**
     * @param mixed $column
     * @return bool|mixed|ActiveRow|Selection|null
     */
    public function &offsetGet($column) {
        return $this->__get($column);
    }

    /**
     * @param mixed $column
     * @param mixed $value
     */
    public function offsetSet($column, $value): void {
        throw new LogicException('Cannot update multiModel directly.');
    }

    /**
     * @param mixed $column
     */
    public function offsetUnset($column): void {
        throw new LogicException('Cannot update multiModel directly.');
    }
}
