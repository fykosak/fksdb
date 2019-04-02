<?php

namespace FKSDB\ORM;

use FKSDB\ORM\Tables\TypedTableSelection;
use InvalidArgumentException;
use ModelException;
use Nette\Database\Connection;
use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\Selection as TableSelection;
use Nette\InvalidStateException;
use PDOException;
use Traversable;

/**
 * Service class to high-level manipulation with ORM objects.
 * Use singleton descedants implemetations.
 *
 * @note Because of compatibility with PHP 5.2 (no LSB), part of the code has to be
 *       duplicated in all descedant classes.
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
abstract class AbstractServiceSingle extends TableSelection implements IService {

    /**
     * @var Connection
     */
    protected $connection;

    /**
     * @var array of AbstractService  singleton instances of descedants
     */
    protected static $instances = [];

    /**
     * FKSDB\ORM\AbstractServiceSingle constructor.
     * @param Connection $connection
     */
    public function __construct(Connection $connection) {
        parent::__construct($this->getTableName(), $connection);
        $this->connection = $connection;
    }

    /**
     * Use this method to create new models!
     *
     * @param Traversable $data
     * @return AbstractModelSingle
     * @throws ModelException
     */
    public function createNew($data = null) {
        $modelClassName = $this->getModelClassName();
        if (!$data) {
            $data = $this->getDefaultData();
        }
        try {
            $result = $this->getTable()->insert($data);
            if ($result !== false) {
                /**
                 * @var AbstractModelSingle $model
                 */
                $model = ($modelClassName)::createFromTableRow($result);
                $model->setNew(false);
                return $model;
            }
        } catch (PDOException $exception) {
            throw new ModelException('Error when storing model.', null, $exception);
        }
        $code = $this->getConnection()->errorCode();
        throw new ModelException("$code: Error when storing a model.");
    }

    /**
     * @internal Used also in MultiTableSelection.
     *
     * @param array $data
     * @return AbstractModelSingle
     */
    public function createFromArray(array $data) {
        $className = $this->getModelClassName();
        $data = $this->filterData($data);
        $result = new $className($data, $this);
        return $result;
    }

    /**
     * @return string
     */
    abstract protected function getModelClassName(): string;

    /**
     * @return string
     */
    abstract protected function getTableName(): string;

    /**
     * @param ActiveRow $row
     * @return mixed
     * @deprecated
     */
    public function createFromTableRow(ActiveRow $row) {
        $className = $this->getModelClassName();
        return new $className($row->toArray(), $row->getTable());
    }

    /**
     * Syntactic sugar.
     *
     * @param int $key
     * @return ActiveRow|null
     */
    public function findByPrimary($key) {
        $result = $this->getTable()->get($key);
        if ($result !== false) {
            return $result;
        } else {
            return null;
        }
    }

    /**
     * Updates values in model from given data.
     *
     * @param IModel $model
     * @param array $data
     * @param boolean $alive
     */
    public function updateModel(IModel $model, $data, $alive = true) {
        $modelClassName = $this->getModelClassName();
        if (!$model instanceof $modelClassName) {
            throw new InvalidArgumentException('Service for class ' . $this->getModelClassName() . ' cannot store ' . get_class($model));
        }

        $data = $this->filterData($data);
        foreach ($data as $key => $value) {
            $model->{$key} = $value;
        }
    }

    /**
     * Use this method to store a model!
     *
     * @param IModel $model
     * @throws InvalidArgumentException
     * @throws ModelException
     */
    public function save(IModel & $model) {
        $modelClassName = $this->getModelClassName();
        if (!$model instanceof $modelClassName) {
            throw new InvalidArgumentException('Service for class ' . $this->getModelClassName() . ' cannot store ' . get_class($model));
        }
        try {
            if ($model->isNew()) {
                $result = $this->getTable()->insert($model->toArray());
                if ($result !== false) {
                    $model = $result;
                    $model->setNew(false);
                } else {
                    $result = false;
                }
            } else {
                $result = $model->update() !== false;
            }
        } catch (PDOException $exception) {
            throw new ModelException('Error when storing model.', null, $exception);
        }
        if (!$result) {
            $code = $this->getConnection()->errorCode();
            throw new ModelException("$code: Error when storing a model.");
        }
    }

    /**
     * Use this method to delete a model!
     * (Name chosen not to collide with parent.)
     *
     * @param IModel $model
     * @throws InvalidArgumentException
     * @throws InvalidStateException
     */
    public function dispose(IModel $model) {
        $modelClassName = $this->getModelClassName();
        if (!$model instanceof $modelClassName) {
            throw new InvalidArgumentException('Service for class ' . $this->getModelClassName() . ' cannot store ' . get_class($model));
        }
        if (!$model->isNew() && $model->delete() === false) {
            $code = $this->getConnection()->errorCode();
            throw new ModelException("$code: Error when deleting a model.");
        }
    }

    /**
     * @return TableSelection
     */
    public function getTable() {
        return new TypedTableSelection($this->getModelClassName(), $this->getTableName(), $this->connection);
    }

    protected $defaults = null;

    /**
     * Default data for the new model.
     *
     * @return array
     */
    protected function getDefaultData() {
        if ($this->defaults == null) {
            $this->defaults = [];
            foreach ($this->getColumnMetadata() as $column) {
                if ($column['nativetype'] == 'TIMESTAMP' && isset($column['default']) && $column['default'] == 'CURRENT_TIMESTAMP') {
                    continue;
                }
                $this->defaults[$column['name']] = isset($column['default']) ? $column['default'] : null;
            }
        }
        return $this->defaults;
    }

    /**
     * Omits array elements whose keys aren't columns in the table.
     *
     * @param array|null $data
     * @return array|null
     */
    protected function filterData($data) {
        if ($data === null) {
            return null;
        }
        $result = [];
        foreach ($this->getColumnMetadata() as $column) {
            $name = $column['name'];
            if (array_key_exists($name, $data)) {
                $result[$name] = $data[$name];
            }
        }
        return $result;
    }

    private $columns;

    /**
     * @return array
     */
    private function getColumnMetadata() {
        if ($this->columns === null) {
            $this->columns = $this->getConnection()->getSupplementalDriver()->getColumns($this->getTableName());
        }
        return $this->columns;
    }

}

