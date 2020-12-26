<?php

namespace FKSDB\Models\ORM\Services;

use FKSDB\Models\Exceptions\ModelException;
use FKSDB\Models\ORM\Models\AbstractModelSingle;
use FKSDB\Models\ORM\Tables\TypedTableSelection;
use InvalidArgumentException;
use Nette\Database\Connection;
use Nette\Database\Conventions;
use Nette\Database\Explorer;
use Nette\Database\Table\Selection;
use PDOException;

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
abstract class AbstractServiceSingle extends Selection {

    private string $modelClassName;
    private string $tableName;

    public function __construct(string $tableName, string $modelClassName, Explorer $connection, Conventions $conventions) {
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
                return ($modelClassName)::createFromActiveRow($result);
            }
        } catch (PDOException $exception) {
            throw new ModelException('Error when storing model.', null, $exception);
        }
        $code = $this->getConnection()->getPdo()->errorCode();
        throw new ModelException("$code: Error when storing a model.");
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
        return $result;
    }

    /**
     * @param AbstractModelSingle $model
     * @return AbstractModelSingle|null
     */
    public function refresh(AbstractModelSingle $model): AbstractModelSingle {
        return $this->findByPrimary($model->getPrimary(true));
    }

    /**
     * @param AbstractModelSingle $model
     * @param array $data
     * @return bool
     * @throws ModelException
     */
    public function updateModel2(AbstractModelSingle $model, array $data): bool {
        try {
            $this->checkType($model);
            $data = $this->filterData($data);
            return $model->update($data);
        } catch (PDOException $exception) {
            throw new ModelException('Error when storing model.', null, $exception);
        }
    }

    /**
     * Use this method to delete a model!
     * (Name chosen not to collide with parent.)
     *
     * @param AbstractModelSingle $model
     * @throws ModelException
     */
    public function dispose(AbstractModelSingle $model): void {
        $this->checkType($model);
        try {
            $model->delete();
        } catch (PDOException $exception) {
            $code = $exception->getCode();
            throw new ModelException("$code: Error when deleting a model.");
        }
    }

    public function getTable(): TypedTableSelection {
        return new TypedTableSelection($this->getModelClassName(), $this->getTableName(), $this->context, $this->conventions);
    }

    public function getConnection(): Connection {
        return $this->context->getConnection();
    }

    public function getContext(): Explorer {
        return $this->context;
    }

    public function getConventions(): Conventions {
        return $this->conventions;
    }

    /**
     * @param AbstractModelSingle $model
     * @throws InvalidArgumentException
     */
    protected function checkType(AbstractModelSingle $model): void {
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
            $this->columns = $this->context->getConnection()->getDriver()->getColumns($this->getTableName());
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
