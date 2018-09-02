<?php

use Nette\Database\Connection;
use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\Selection as TableSelection;
use Nette\InvalidStateException;
use ORM\IModel;
use ORM\IService;
use ORM\Tables\TypedTableSelection;

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
     * @var string
     */
    protected $modelClassName;

    /**
     * @var string
     */
    protected $tableName;

    /**
     * @var Connection
     */
    protected $connection;

    /**
     * @var array of AbstractService  singleton instances of descedants
     */
    protected static $instances = array();

    public function __construct(Connection $connection) {
        parent::__construct($this->tableName, $connection);
        $this->connection = $connection;
    }

    /**
     * Use this method to create new models!
     *
     * @param array $data
     * @return AbstractModelSingle
     */
    public function createNew($data = null) {
        if ($data === null) {
            $data = $this->getDefaultData();
        }
        $result = $this->createFromArray((array)$data);
        $result->setNew();
        return $result;
    }

    /**
     * @internal Used also in MultiTableSelection.
     *
     * @param array $data
     * @return AbstractModelSingle
     */
    public function createFromArray(array $data) {
        $className = $this->modelClassName;
        $data = $this->filterData($data);
        $result = new $className($data, $this);
        return $result;
    }

    public function createFromTableRow(ActiveRow $row) {
        $className = $this->modelClassName;
        return new $className($row->toArray(), $row->getTable());
    }

    /**
     * Syntactic sugar.
     *
     * @param int $key
     * @return AbstractModelSingle|null
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
     * @param AbstractModelSingle $model
     * @param boolean $alive
     * @param array $data
     */
    public function updateModel(IModel $model, $data, $alive = true) {
        if (!$model instanceof $this->modelClassName) {
            throw new InvalidArgumentException('Service for class ' . $this->modelClassName . ' cannot store ' . get_class($model));
        }

        $data = $this->filterData($data);
        foreach ($data as $key => $value) {
            $model->{$key} = $value;
        }
    }

    /**
     * Use this method to store a model!
     *
     * @param AbstractModelSingle $model
     * @throws InvalidArgumentException
     * @throws ModelException
     */
    public function save(IModel & $model) {
        if (!$model instanceof $this->modelClassName) {
            throw new InvalidArgumentException('Service for class ' . $this->modelClassName . ' cannot store ' . get_class($model));
        }
        $result = true;
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
        } catch (PDOException $e) {
            throw new ModelException('Error when storing model.', null, $e);
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
     * @param AbstractModelSingle $model
     * @throws InvalidArgumentException
     * @throws InvalidStateException
     */
    public function dispose(IModel $model) {
        if (!$model instanceof $this->modelClassName) {
            throw new InvalidArgumentException('Service for class ' . $this->modelClassName . ' cannot store ' . get_class($model));
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
        return new TypedTableSelection($this->modelClassName, $this->tableName, $this->connection);
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
        $result = array();
        foreach ($this->getColumnMetadata() as $column) {
            $name = $column['name'];
            if (array_key_exists($name, $data)) {
                $result[$name] = $data[$name];
            }
        }
        return $result;
    }

    private $columns;

    private function getColumnMetadata() {
        if ($this->columns === null) {
            $this->columns = $this->getConnection()->getSupplementalDriver()->getColumns($this->tableName);
        }
        return $this->columns;
    }

}

