<?php

namespace FKSDB\Models\ORM\ModelsMulti;

use FKSDB\Models\ORM\Models\OldAbstractModelSingle;
use FKSDB\Models\ORM\ServicesMulti\AbstractServiceMulti;
use LogicException;
use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\Selection;
use Nette\SmartObject;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 * @property-read OldAbstractModelSingle $mainModel
 */
abstract class AbstractModelMulti extends ActiveRow {

    use SmartObject;

    public OldAbstractModelSingle $mainModel;
    public OldAbstractModelSingle $joinedModel;

    /**
     * @note DO NOT use directly, use AbstractServiceMulti::composeModel
     *
     * @param AbstractServiceMulti|null $service
     * @param OldAbstractModelSingle $mainModel
     * @param OldAbstractModelSingle $joinedModel
     */
    public function __construct(?AbstractServiceMulti $service, OldAbstractModelSingle $mainModel, OldAbstractModelSingle $joinedModel) {
        parent::__construct($joinedModel->toArray(), $joinedModel->getTable());
        if (is_null($service)) {
            $this->joinedModel = $joinedModel;
            $this->mainModel = $mainModel;
        } else {
            $this->joinedModel = $joinedModel;
            $this->setMainModel($mainModel, $service);
        }
    }

    public function toArray(): array {
        return $this->mainModel->toArray() + $this->joinedModel->toArray();
    }

    public function setMainModel(OldAbstractModelSingle $mainModel, AbstractServiceMulti $service): void {
        $this->mainModel = $mainModel;
        if (!$mainModel->isNew() && $this->joinedModel) { // bind via foreign key
            $this->joinedModel->{$service->joiningColumn} = $mainModel->getPrimary();
        }
    }
        public function setJoinedModel(OldAbstractModelSingle $joinedModel): void {
        $this->joinedModel = $joinedModel;
    }

    /**
     * @param string|int $key
     * @return bool|mixed|ActiveRow|Selection|null
     */
    public function &__get(string $key) {
        if ($this->mainModel->__isset($key)) {
            return $this->mainModel->__get($key);
        }
        if ($this->joinedModel->__isset($key)) {
            return $this->joinedModel->__get($key);
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
        return $this->mainModel->__isset($name) || $this->joinedModel->__isset($name);
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
        return $this->joinedModel->getPrimary($throw);
    }

    public function getSignature(bool $throw = true): string {
        return implode('|', (array)$this->getPrimary($throw));
    }

    /**
     * @return bool
     * @deprecated
     */
    public function isNew(): bool {
        return $this->joinedModel->isNew();
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
