<?php

namespace FKSDB\ORM;

use FKSDB\ORM\Tables\MultiTableSelection;
use InvalidArgumentException;
use Nette\InvalidStateException;
use Nette\SmartObject;

/**
 * Service for object representing one side of M:N relation, or entity in is-a relation ship.
 * Joined side is in a sense primary (search, select, delete).
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
abstract class AbstractServiceMulti implements IService {
    use SmartObject;

    /**
     * @var AbstractServiceSingle
     */
    protected $mainService;

    /**
     * @var AbstractServiceSingle
     */
    protected $joinedService;

    /**
     * AbstractServiceMulti constructor.
     * @param AbstractServiceSingle $mainService
     * @param AbstractServiceSingle $joinedService
     */
    public function __construct(AbstractServiceSingle $mainService, AbstractServiceSingle $joinedService) {
        $this->mainService = $mainService;
        $this->joinedService = $joinedService;
    }

    /**
     * Use this method to create new models!
     *
     * @param array $data
     * @return AbstractModelMulti
     * @deprecated
     */
    public function createNew($data = null) {
        $mainModel = $this->getMainService()->createNew($data);
        $joinedModel = $this->getJoinedService()->createNew($data);

        $className = $this->getModelClassName();
        return new $className($this, $mainModel, $joinedModel);
    }

    /**
     * Use this method to create new models!
     *
     * @param array $data
     * @return AbstractModelMulti
     */
    public function createNewModel(array $data): IModel {
        $mainModel = $this->getMainService()->createNewModel($data);
        $data[$this->getJoiningColumn()] = $mainModel->{$this->getJoiningColumn()};
        $joinedModel = $this->getJoinedService()->createNewModel($data);

        $className = $this->getModelClassName();
        return new $className($this, $mainModel, $joinedModel);
    }

    public function composeModel(AbstractModelSingle $mainModel, AbstractModelSingle $joinedModel): AbstractModelMulti {
        $className = $this->getModelClassName();
        return new $className($this, $mainModel, $joinedModel);
    }

    /**
     * @param IModel|AbstractModelMulti $model
     * @param $data
     * @param bool $alive
     * @return void
     */
    public function updateModel(IModel $model, $data, $alive = true) {
        $this->checkType($model);
        $this->getMainService()->updateModel($model->getMainModel(), $data, $alive);
        $this->getJoinedService()->updateModel($model->getJoinedModel(), $data, $alive);
    }

    /**
     * @param IModel|AbstractModelMulti $model
     * @param $data
     */
    public function updateModel2(IModel $model, array $data) {
        $this->checkType($model);
        $this->getMainService()->updateModel2($model->getMainModel(), $data);
        $this->getJoinedService()->updateModel2($model->getJoinedModel(), $data);
    }

    /**
     * Use this method to store a model!
     *
     * @param IModel|AbstractModelMulti $model
     */
    public function save(IModel &$model) {
        $this->checkType($model);

        $mainModel = $model->getMainModel();
        $joinedModel = $model->getJoinedModel();
        $this->getMainService()->save($mainModel);
        //update ID when it was new
        $model->setService($this);
        $model->setMainModel($mainModel);
        $this->getJoinedService()->save($joinedModel);
        $model->setJoinedModel($joinedModel);
    }

    /**
     * Use this method to delete a model!
     *
     * @param IModel|AbstractModelMulti $model
     * @throws InvalidArgumentException
     * @throws InvalidStateException
     */
    public function dispose(IModel $model) {
        $this->checkType($model);
        $this->getJoinedService()->dispose($model->getJoinedModel());
        //TODO here should be deletion of mainModel as well, consider parametrizing this
    }

    public function getMainService(): AbstractServiceSingle {
        return $this->mainService;
    }

    public function getJoinedService(): AbstractServiceSingle {
        return $this->joinedService;
    }

    /**
     *
     * @param int $key ID of the joined models
     * @return AbstractModelMulti|null
     */
    public function findByPrimary($key) {
        $joinedModel = $this->getJoinedService()->findByPrimary($key);
        if (!$joinedModel) {
            return null;
        }
        /** @var AbstractModelSingle $mainModel */
        $mainModel = $this->getMainService()
            ->getTable()
            ->where($this->getJoiningColumn(), $joinedModel->{$this->getJoiningColumn()})
            ->fetch(); //?? is this always unique??
        return $this->composeModel($mainModel, $joinedModel);
    }

    public function getTable(): MultiTableSelection {
        $joinedTable = $this->getJoinedService()->getTable()->getName();
        $mainTable = $this->getMainService()->getTable()->getName();

        $selection = new MultiTableSelection($this, $joinedTable, $this->getJoinedService()->getContext(), $this->getJoinedService()->getConventions());
        $selection->select("$joinedTable.*");
        $selection->select("$mainTable.*");

        return $selection;
    }

    /**
     * @param IModel $model
     * @throws InvalidArgumentException
     */
    protected function checkType(IModel $model) {
        $className = $this->getModelClassName();
        if (!$model instanceof $className) {
            throw new InvalidArgumentException('Service for class ' . $this->getModelClassName() . ' cannot store ' . get_class($model));
        }
    }

    abstract public function getJoiningColumn(): string;
}
