<?php

namespace FKSDB\ORM;

use FKSDB\ORM\Tables\MultiTableSelection;
use InvalidArgumentException;
use Nette\InvalidStateException;
use Nette\Object;

/**
 * Service for object representing one side of M:N relation, or entity in is-a relation ship.
 * Joined side is in a sense primary (search, select, delete).
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
abstract class AbstractServiceMulti extends Object implements IService {

    /**
     * @var string
     */
    protected $modelClassName;

    /**
     * @var AbstractServiceSingle
     */
    protected $mainService;

    /**
     * @var AbstractServiceSingle
     */
    protected $joinedService;
    /**
     * @var string
     */
    protected $joiningColumn;

    /**
     * @var array of AbstractService  singleton instances of descedants
     */
    protected static $instances = [];

    /**
     *
     * @param AbstractServiceSingle $mainService
     * @param AbstractServiceSingle $joinedService
     */
    public function __construct($mainService, $joinedService) {
        $this->setMainService($mainService);
        $this->setJoinedService($joinedService);
    }

    /**
     * Use this method to create new models!
     *
     * @param array $data
     * @return AbstractModelMulti
     */
    public function createNew($data = null) {
        $mainModel = $this->getMainService()->createNew($data);
        $joinedModel = $this->getJoinedService()->createNew($data);

        $className = $this->modelClassName;
        $result = new $className($this, $mainModel, $joinedModel);
        return $result;
    }

    /**
     *
     * @param AbstractModelSingle $mainModel
     * @param AbstractModelSingle $joinedModel
     * @return AbstractModelMulti
     */
    public function composeModel(AbstractModelSingle $mainModel, AbstractModelSingle $joinedModel) {
        $className = $this->modelClassName;
        $result = new $className($this, $mainModel, $joinedModel);
        return $result;
    }

    /**
     * @param IModel $model
     * @param $data
     * @param bool $alive
     * @return mixed|void
     */
    public function updateModel(IModel $model, $data, $alive = true) {
        if (!$model instanceof $this->modelClassName) {
            throw new InvalidArgumentException('Service for class ' . $this->modelClassName . ' cannot store ' . get_class($model));
        }
        $this->getMainService()->updateModel($model->getMainModel(), $data, $alive);
        $this->getJoinedService()->updateModel($model->getJoinedModel(), $data, $alive);
    }

    /**
     * Use this method to store a model!
     *
     * @param IModel $model
     */
    public function save(IModel &$model) {
        if (!$model instanceof $this->modelClassName) {
            throw new InvalidArgumentException('Service for class ' . $this->modelClassName . ' cannot store ' . get_class($model));
        }

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
     * @param IModel $model
     * @throws InvalidArgumentException
     * @throws InvalidStateException
     */
    public function dispose(IModel $model) {
        if (!$model instanceof $this->modelClassName) {
            throw new InvalidArgumentException('Service for class ' . $this->modelClassName . ' cannot delete ' . get_class($model));
        }
        $this->getJoinedService()->dispose($model->getJoinedModel());
        //TODO here should be deletion of mainModel as well, consider parametrizing this
    }

    /**
     * @return AbstractServiceSingle
     */
    public function getMainService() {
        return $this->mainService;
    }

    /**
     * @param AbstractServiceSingle $mainService
     */
    protected function setMainService(AbstractServiceSingle $mainService) {
        $this->mainService = $mainService;
    }

    /**
     * @return AbstractServiceSingle
     */
    public function getJoinedService() {
        return $this->joinedService;
    }

    /**
     * @param AbstractServiceSingle $joinedService
     */
    protected function setJoinedService(AbstractServiceSingle $joinedService) {
        $this->joinedService = $joinedService;
    }

    /**
     * @return string
     */
    public function getJoiningColumn() {
        return $this->joiningColumn;
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
        $this->modelClassName;
        $mainModel = $this->getMainService()
            ->getTable()
            ->where($this->joiningColumn, $joinedModel->{$this->joiningColumn})
            ->fetch(); //?? is this always unique??
        return $this->composeModel($mainModel, $joinedModel);
    }

    /**
     * @return \Nette\Database\Table\Selection|MultiTableSelection
     */
    public function getTable() {
        $joinedTable = $this->getJoinedService()->getTable()->getName();
        $mainTable = $this->getMainService()->getTable()->getName();

        $selection = new MultiTableSelection($this, $joinedTable, $this->getJoinedService()->getConnection());
        $selection->select("$joinedTable.*");
        $selection->select("$mainTable.*");

        return $selection;
    }

}


