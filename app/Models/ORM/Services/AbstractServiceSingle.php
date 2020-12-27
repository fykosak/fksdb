<?php

namespace FKSDB\Models\ORM\Services;


use FKSDB\Models\ORM\Models\AbstractModelSingle;
use FKSDB\Models\ORM\IModel;
use FKSDB\Models\ORM\IService;
use Fykosak\Utils\ORM\AbstractService;
use Fykosak\Utils\ORM\Exceptions\ModelException;

use InvalidArgumentException;
use Nette\Database\Context;
use Nette\Database\IConventions;
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
abstract class AbstractServiceSingle extends AbstractService implements IService {

    public function __construct(Context $connection, IConventions $conventions, string $tableName, string $modelClassName) {
        parent::__construct($connection, $conventions, $tableName, $modelClassName);
    }

    /**
     * @param array $data
     * @return AbstractModelSingle
     * @throws ModelException
     */
    public function createNewModel(array $data): AbstractModelSingle {
        $model = parent::createNewModel($data);
        $model->setNew(false); // only for old compatibility
        return $model;
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

    private array $columns;

    private function getColumnMetadata(): array {
        if (!isset($this->columns)) {
            $this->columns = $this->context->getConnection()->getSupplementalDriver()->getColumns($this->getTableName());
        }
        return $this->columns;
    }
}
