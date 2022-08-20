<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\ModelsMulti;

use Fykosak\NetteORM\Model;
use Nette\Database\Table\Selection;

/**
 * @deprecated
 */
abstract class ModelMulti extends Model
{
    public Model $mainModel;
    public Model $joinedModel;

    /**
     * @note DO NOT use directly, use ServiceMulti::composeModel
     */
    public function __construct(Model $mainModel, Model $joinedModel)
    {
        parent::__construct($joinedModel->toArray(), $joinedModel->getTable());
        $this->joinedModel = $joinedModel;
        $this->mainModel = $mainModel;
    }

    public function toArray(): array
    {
        return $this->mainModel->toArray() + parent::toArray();
    }

    /**
     * @return bool|mixed|Selection|null
     * @throws \ReflectionException
     */
    public function &__get(string $key)
    {
        if ($this->mainModel->__isset($key)) {
            return $this->mainModel->__get($key);
        }
        if (parent::__isset($key)) {
            return parent::__get($key);
        }
        // this reference isn't that important
        $null = null;
        return $null;
    }

    /**
     * @param string|int $key
     */
    public function __isset($key): bool
    {
        return $this->mainModel->__isset($key) || parent::__isset($key);
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
     * @return bool|mixed|Selection|null
     * @throws \ReflectionException
     */
    public function &offsetGet($column)
    {
        return $this->__get($column);
    }
}
