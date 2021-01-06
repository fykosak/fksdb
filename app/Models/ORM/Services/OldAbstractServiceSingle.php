<?php

namespace FKSDB\Models\ORM\Services;

use FKSDB\Models\Exceptions\ModelException;
use FKSDB\Models\ORM\IModel;
use FKSDB\Models\ORM\IService;
use FKSDB\Models\ORM\Models\AbstractModelSingle;
use FKSDB\Models\ORM\Models\OldAbstractModelSingle;
use InvalidArgumentException;
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
abstract class OldAbstractServiceSingle extends AbstractServiceSingle implements IService {

    /**
     * Use this method to create new models!
     *
     * @param iterable|null $data
     * @return OldAbstractModelSingle
     * @throws ModelException
     * @deprecated use createNewModel
     */
    public function createNew(iterable $data = null): OldAbstractModelSingle {
        if ($data === null) {
            $data = $this->getDefaultData();
        }
        $result = $this->createFromArray((array)$data);
        $result->setNew();
        return $result;
    }

    /**
     * @param array $data
     * @return OldAbstractModelSingle
     * @deprecated
     * @internal Used also in MultiTableSelection.
     */
    public function createFromArray(array $data): OldAbstractModelSingle {
        $className = $this->getModelClassName();
        $data = $this->filterData($data);
        return new $className($data, $this);
    }

    /**
     * Use this method to delete a model!
     * (Name chosen not to collide with parent.)
     *
     * @param IModel|AbstractModelSingle $model
     * @throws ModelException
     */
    public function dispose($model): void {
        $this->checkType($model);
        if (!$model->isNew() && $model->delete() === false) {
            $code = $this->context->getConnection()->getPdo()->errorCode();
            throw new ModelException("$code: Error when deleting a model.");
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
     * Use this method to store a model!
     *
     * @param IModel|OldAbstractModelSingle $model
     * @throws ModelException
     * @deprecated
     */
    public function save(IModel &$model): void {
        $modelClassName = $this->getModelClassName();
        /** @var OldAbstractModelSingle $model */
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
}
