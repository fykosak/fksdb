<?php

use Nette\InvalidStateException;
use Nette\Object;
use ORM\IModel;
use ORM\IService;
use ORM\Tables\MultiTableSelection;

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
     * @var array of AbstractService  singleton instances of descedants
     */
    protected static $instances = array();

    /**
     *
     * @param AbstractServiceSingle $mainService
     * @param AbstractServiceSingle $joinedService
     * @param string $modelClassName
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

    public function updateModel(IModel $model, $data) {
        if (!$model instanceof $this->modelClassName) {
            throw new InvalidArgumentException('Service for class ' . $this->modelClassName . ' cannot store ' . get_class($model));
        }

        $this->getMainService()->updateModel($model->getMainModel(), $data);
        $this->getJoinedService()->updateModel($model->getJoinedModel(), $data);
    }

    /**
     * Use this method to store a model!
     *
     * @param AbstractModelMulti $model
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
     * @param AbstractModelMulti $model
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

    public function getMainService() {
        return $this->mainService;
    }

    protected function setMainService(AbstractServiceSingle $mainService) {
        $this->mainService = $mainService;
    }

    public function getJoinedService() {
        return $this->joinedService;
    }

    protected function setJoinedService(AbstractServiceSingle $joinedService) {
        $this->joinedService = $joinedService;
    }

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

    public function getTable() {
        $joinedTable = $this->getJoinedService()->getTable()->getName();
        $mainTable = $this->getMainService()->getTable()->getName();

        $selection = new MultiTableSelection($this, $joinedTable, $this->getJoinedService()->getConnection());
        $selection->select("$joinedTable.*");
        $selection->select("$mainTable.*");

        return $selection;
    }

}

?>
