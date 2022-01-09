<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\ModelsMulti;

use Fykosak\NetteORM\AbstractModel;
use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\Selection;
use Nette\SmartObject;

/**
 * @deprecated
 */
abstract class AbstractModelMulti extends ActiveRow
{
    use SmartObject;

    public AbstractModel $mainModel;
    public AbstractModel $joinedModel;

    /**
     * @note DO NOT use directly, use AbstractServiceMulti::composeModel
     */
    public function __construct(AbstractModel $mainModel, AbstractModel $joinedModel)
    {
        parent::__construct($joinedModel->toArray(), $joinedModel->getTable());
        $this->joinedModel = $joinedModel;
        $this->mainModel = $mainModel;
    }

    public function toArray(): array
    {
        return $this->mainModel->toArray() + $this->joinedModel->toArray();
    }

    /**
     * @return bool|mixed|ActiveRow|Selection|null
     */
    public function &__get(string $key)
    {
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
     */
    public function __isset($name): bool
    {
        return $this->mainModel->__isset($name) || $this->joinedModel->__isset($name);
    }

    /**
     * @param string $column
     * @param mixed $value
     */
    public function __set($column, $value): void
    {
        throw new \LogicException('Cannot update multiModel directly.');
    }

    /**
     * @param string|int $key
     */
    public function __unset($key): void
    {
        throw new \LogicException('Cannot update multiModel directly.');
    }

    /**
     * @param bool $throw
     * @return mixed
     */
    public function getPrimary($throw = true)
    {
        return $this->joinedModel->getPrimary($throw);
    }

    /**
     * @param mixed $column
     */
    public function offsetExists($column): bool
    {
        return $this->__isset($column);
    }

    /**
     * @param mixed $column
     * @return bool|mixed|ActiveRow|Selection|null
     */
    public function &offsetGet($column)
    {
        return $this->__get($column);
    }

    /**
     * @param mixed $column
     * @param mixed $value
     */
    public function offsetSet($column, $value): void
    {
        throw new \LogicException('Cannot update multiModel directly.');
    }

    /**
     * @param mixed $column
     */
    public function offsetUnset($column): void
    {
        throw new \LogicException('Cannot update multiModel directly.');
    }
}
