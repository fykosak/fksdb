<?php

namespace FKSDB\ORM;

use FKSDB\Exceptions\ModelException;
use FKSDB\ORM\Tables\TypedTableSelection;
use InvalidArgumentException;
use Nette\Database\Connection;
use Nette\Database\Context;
use Nette\Database\IConventions;
use Nette\Database\Table\Selection;
use Nette\InvalidStateException;
use PDOException;
use Tracy\Debugger;

/**
 * Service class to high-level manipulation with ORM objects.
 * Use singleton descendant implementations.
 *
 * @note Because of compatibility with PHP 5.2 (no LSB), part of the code has to be
 *       duplicated in all descendant classes.
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 * @author Michal Červeňak <miso@fykos.cz>
 */
abstract class AbstractServiceSingle extends Selection implements IService {

    private string $modelClassName;

    private string $tableName;

    public function __construct(Context $connection, IConventions $conventions, string $tableName, string $modelClassName) {
        $this->tableName = $tableName;
        $this->modelClassName = $modelClassName;
        parent::__construct($connection, $conventions, $tableName);
    }

    /**
     * @param array $data
     * @return AbstractModelSingle
     * @throws ModelException
     */
    public function createNewModel(array $data): AbstractModelSingle {
        $modelClassName = $this->getModelClassName();
        $data = $this->filterData($data);
        try {
            $result = $this->getTable()->insert($data);
            if ($result !== false) {
                $model = ($modelClassName)::createFromActiveRow($result);
                $model->setNew(false); // only for old compatibility
                return $model;
            }
        } catch (PDOException $exception) {
            throw new ModelException('Error when storing model.', null, $exception);
        }
        $code = $this->getConnection()->getPdo()->errorCode();
        throw new ModelException("$code: Error when storing a model.");
    }


    /**
     * Use this method to create new models!
     *
     * @param iterable|null $data
     * @return AbstractModelSingle
     * @throws ModelException
     * @deprecated use createNewModel
     */
    public function createNew(iterable $data = null) {
        if ($data === null) {
            $data = $this->getDefaultData();
        }
        $result = $this->createFromArray((array)$data);
        $result->setNew();
        return $result;
    }

    /**
     * @param array $data
     * @return AbstractModelSingle
     * @deprecated
     * @internal Used also in MultiTableSelection.
     */
    public function createFromArray(array $data) {
        $className = $this->getModelClassName();
        $data = $this->filterData($data);
        return new $className($data, $this);
    }

    /**
     * Syntactic sugar.
     *
     * @param mixed $key
     * @return AbstractModelSingle|null
     */
    public function findByPrimary($key): ?AbstractModelSingle {
        /** @var AbstractModelSingle|null $result */
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
     * @param iterable $data
     * @param bool $alive
     * @deprecated
     */
    public function updateModel(IModel $model, iterable $data, bool $alive = true): void {
        $modelClassName = $this->getModelClassName();
        if (!$model instanceof $modelClassName) {
            throw new InvalidArgumentException('Service for class ' . $this->getModelClassName() . ' cannot store ' . get_class($model));
        }

        $data = $this->filterData((array)$data);
        foreach ($data as $key => $value) {
            $model->{$key} = $value;
        }
    }

    /**
     * @param AbstractModelSingle|IModel $model
     * @return AbstractModelSingle|null
     */
    public function refresh(AbstractModelSingle $model): AbstractModelSingle {
        return $this->findByPrimary($model->getPrimary(true));
    }

    /**
     * @param AbstractModelSingle|IModel $model
     * @param array $data
     * @return bool
     */
    public function updateModel2(IModel $model, array $data): bool {
        try {
            $this->checkType($model);
            $data = $this->filterData($data);
            return $model->update($data);
        } catch (PDOException $exception) {
            throw new ModelException('Error when storing model.', null, $exception);
        }
    }

    /**
     * Use this method to store a model!
     *
     * @param IModel|AbstractModelSingle $model
     * @throws InvalidArgumentException
     * @throws ModelException
     * @deprecated
     */
    public function save(IModel &$model): void {
        $modelClassName = $this->getModelClassName();
        /** @var AbstractModelSingle $model */
        if (!$model instanceof $modelClassName) {
            throw new InvalidArgumentException('Service for class ' . $this->getModelClassName() . ' cannot store ' . get_class($model));
        }
        try {
            if ($model->isNew()) {
                $result = $this->getTable()->insert($model->getTmpData());
                if ($result !== false) {
                    $model = $modelClassName::createFromActiveRow($result);
                    $model->setNew(false);
                }
            } else {
                $model->update($model->getTmpData());
            }
        } catch (PDOException $exception) {
            Debugger::log($exception);
            throw new ModelException('Error when storing model.', null, $exception);
        }
        // because ActiveRow return false when 0 rows where effected https://stackoverflow.com/questions/11813911/php-pdo-error-number-00000-when-query-is-correct
        if (!(int)$this->context->getConnection()->getPdo()->errorInfo()) {
            $code = $this->context->getConnection()->getPdo()->errorCode();
            throw new ModelException("$code: Error when storing a model.");
        }
    }

    /**
     * Use this method to delete a model!
     * (Name chosen not to collide with parent.)
     *
     * @param IModel|AbstractModelSingle $model
     * @throws InvalidArgumentException
     * @throws InvalidStateException
     */
    public function dispose(IModel $model): void {
        $this->checkType($model);
        if (!$model->isNew() && $model->delete() === false) {
            $code = $this->context->getConnection()->getPdo()->errorCode();
            throw new ModelException("$code: Error when deleting a model.");
        }
    }

    public function getTable(): TypedTableSelection {
        return new TypedTableSelection($this->getModelClassName(), $this->getTableName(), $this->context, $this->conventions);
    }

    public function getConnection(): Connection {
        return $this->context->getConnection();
    }

    public function getContext(): Context {
        return $this->context;
    }

    public function getConventions(): IConventions {
        return $this->conventions;
    }

    /**
     * @param AbstractModelSingle|IModel $model
     * @throws InvalidArgumentException
     */
    private function checkType(AbstractModelSingle $model): void {
        $modelClassName = $this->getModelClassName();
        if (!$model instanceof $modelClassName) {
            throw new InvalidArgumentException('Service for class ' . $this->getModelClassName() . ' cannot store ' . get_class($model));
        }
    }

    protected ?array $defaults = null;

    /**
     * Default data for the new model.
     * TODO is this really needed?
     * @return array
     */
    protected function getDefaultData(): array {
        if (!isset($this->defaults)) {
            $this->defaults = [];
            foreach ($this->getColumnMetadata() as $column) {
                if ($column['nativetype'] == 'TIMESTAMP' && isset($column['default'])
                    && !preg_match('/^[0-9]{4}/', $column['default'])) {
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
     * @param array $data
     * @return array
     */
    protected function filterData(array $data): array {
        $result = [];
        foreach ($this->getColumnMetadata() as $column) {
            $name = $column['name'];
            if (array_key_exists($name, $data)) {
                $result[$name] = $data[$name];
            }
        }
        return $result;
    }

    private array $columns;

    private function getColumnMetadata(): array {
        if (!isset($this->columns)) {
            $this->columns = $this->context->getConnection()->getSupplementalDriver()->getColumns($this->getTableName());
        }
        return $this->columns;
    }

    final protected function getTableName(): string {
        return $this->tableName;
    }

    /** @return string|AbstractModelSingle */
    final public function getModelClassName(): string {
        return $this->modelClassName;
    }
}
