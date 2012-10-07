<?php

/**
 * Service class to high-level manipulation with ORM objects.
 * Use singleton descedants implemetations.
 * 
 * @note Because of compatibility with PHP 5.2 (no LSB), part of the code has to be
 *       duplicated in all descedant classes.
 * 
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
abstract class AbstractServiceSingle extends NTableSelection {

    /**
     * @var string
     */
    protected $modelClassName;

    /**
     * @var string
     */
    protected $tableName;

    /**
     * @var array of AbstractService  singleton instances of descedants
     */
    protected static $instances = array();

    public function __construct(NConnection $connection) {
        parent::__construct($this->tableName, $connection);
    }

    /**
     * Use this method to create new models!
     * 
     * @param array $data
     * @return AbstractModelSingle
     */
    public function createNew($data = null) {
        $className = $this->modelClassName;
        if ($data === null) {
            $data = $this->getDefaultData();
        }
        $result = new $className($data, $this);
        $result->setNew();
        return $result;
    }

    /**
     * Syntactic sugar.
     * 
     * @param int $key
     * @return AbstractModelSingle|null
     */
    public function findByPrimary($key) {
        $result = $this->find($key)->fetch();
        if ($result !== false) {
            return $result;
        } else {
            return null;
        }
    }

    /**
     * Use this method to store a model!
     * 
     * @param AbstractModelSingle $model
     * @throws InvalidArgumentException
     * @throws InvalidStateException
     */
    public function save(AbstractModelSingle & $model) {
        if (!$model instanceof $this->modelClassName) {
            throw new InvalidArgumentException('Service for class ' . $this->modelClassName . ' cannot store ' . get_class($model));
        }
        if ($model->isNew()) {
            $result = $this->insert($model->toArray());
            if ($result !== false) {
                $model = $result;
            } else {
                throw new InvalidStateException('Error when storing a model.'); //TODO expressive description
            }
        } else {
            if ($model->update() === false) {
                throw new InvalidStateException('Error when storing a model.'); //TODO expressive description
            }
        }
    }

    /**
     * Use this method to delete a model!
     * (Name chosen not to collide with parent.)
     * 
     * @param AbstractModelSingle $model
     * @throws InvalidArgumentException
     * @throws InvalidStateException
     */
    public function dispose(AbstractModelSingle $model) {
        if (!$model instanceof $this->modelClassName) {
            throw new InvalidArgumentException('Service for class ' . $this->modelClassName . ' cannot store ' . get_class($model));
        }
        if ($model->delete() === false) {
            throw new InvalidStateException('Error when deleting a model.'); //TODO expressive description
        }
    }

    protected $defaults = null;

    /**
     * Default data for the new model.
     * 
     * @return array
     */
    protected function getDefaultData() {
        if ($this->defaults == null) {
            $this->defaults = array();
            foreach ($this->getConnection()->getSupplementalDriver()->getColumns($this->name) as $column) {
                $this->defaults[$column['name']] = isset($column['default']) ? $column['default'] : null;
            }
        }
        return $this->defaults;
    }

    /**
     * This override ensures returned objects are of correct class.
     * 
     * @param array $row
     * @return AbstractModelSingle
     */
    protected function createRow(array $row) {
        $className = $this->modelClassName;
        return new $className($row, $this);
    }

}

?>
