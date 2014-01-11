<?php

use Nette\InvalidStateException;
use Nette\Object;
use ORM\IService;

/**
 * Service for object representing one side of M:N relation, or entity in is-a relation ship.
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
        $mainModel = $this->getMainService()->createNew($this->getMainService()->filterData($data));
        $joinedModel = $this->getJoinedService()->createNew($this->getJoinedService()->filterData($data));

        $className = $this->modelClassName;
        $result = new $className($mainModel, $joinedModel);
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
        $result = new $className($mainModel, $joinedModel);
        return $result;
    }

    /**
     * Use this method to store a model!
     * 
     * @param AbstractModelMulti $model
     */
    public function save(AbstractModelMulti $model) {
        if (!$model instanceof $this->modelClassName) {
            throw new InvalidArgumentException('Service for class ' . $this->modelClassName . ' cannot store ' . get_class($model));
        }

        $mainModel = $model->getMainModel();
        $joinedModel = $model->getJoinedModel();
        $this->getMainService()->save($mainModel);
        //update ID when it was new
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
    public function dispose(AbstractModelMulti $model) {
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

}

?>
