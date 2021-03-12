<?php

namespace FKSDB\Models\ORM\Services;

use Fykosak\NetteORM\AbstractService;
use Fykosak\NetteORM\Exceptions\ModelException;
use FKSDB\Models\ORM\IModel;
use FKSDB\Models\ORM\IService;
use Fykosak\NetteORM\AbstractModel;
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
 * @deprecated
 * @use AbstractServiceSingle
 */
abstract class OldAbstractServiceSingle extends AbstractService implements IService {

    /**
     * Use this method to create new models!
     *
     * @param iterable|null $data
     * @return OldAbstractModelSingle
     * @throws ModelException
     * @deprecated use createNewModel
     */
    public function createNew(?iterable $data = null): OldAbstractModelSingle {
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
        return new $className($data, $this->getTable());
    }

    /**
     * Use this method to delete a model!
     * (Name chosen not to collide with parent.)
     *
     * @param IModel|AbstractModel $model
     * @throws ModelException
     */
    public function dispose($model): void {
        $this->checkType($model);
        if (!$model->isNew()) {
            try {
                $model->delete();
            } catch (PDOException $exception) {
                $code = $exception->getCode();
                throw new ModelException("$code: Error when deleting a model.");
            }
        }
    }

    /**
     * Updates values in model from given data.
     *
     * @param IModel $model
     * @param iterable|null $data
     * @param bool $alive
     * @deprecated
     */
    public function updateModel(IModel $model, ?iterable $data, bool $alive = true): void {
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
        /** @var OldAbstractModelSingle $model */
        $this->checkType($model);
        try {
            if ($model->isNew()) {
                $model = $this->createNewModel($model->getTmpData());
                $model->setNew(false);
            } else {
                $this->updateModel2($model, $model->getTmpData());
                $model = $this->refresh($model);
            }
        } catch (PDOException $exception) {
            Debugger::log($exception);
            throw new ModelException('Error when storing model.', null, $exception);
        }
        // because ActiveRow return false when 0 rows where effected https://stackoverflow.com/questions/11813911/php-pdo-error-number-00000-when-query-is-correct
        if (!(int)$this->getExplorer()->getConnection()->getPdo()->errorInfo()) {
            $code = $this->getExplorer()->getConnection()->getPdo()->errorCode();
            throw new ModelException("$code: Error when storing a model.");
        }
    }
}
