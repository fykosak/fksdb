<?php

/**
 * @note Because of compatibility with PHP 5.2 (no LSB), part of the code has to be
 *       duplicated in all descedant classes.
 * 
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
abstract class AbstractServiceMulti extends NObject {

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
        $mainModel = $this->getMainService()->createNew($this->filterData($data, $this->getMainService()));
        $joinedModel = $this->getJoinedService()->createNew($this->filterData($data, $this->getJoinedService()));

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
            throw new InvalidArgumentException('Service for class ' . $this->modelClassName . ' cannot store ' . get_class($model));
        }
        $this->getJoinedService()->delete($model->getJoinedModel());
    }

    protected function getMainService() {
        return $this->mainService;
    }

    protected function setMainService(AbstractServiceSingle $mainService) {
        $this->mainService = $mainService;
    }

    protected function getJoinedService() {
        return $this->joinedService;
    }

    protected function setJoinedService(AbstractServiceSingle $joinedService) {
        $this->joinedService = $joinedService;
    }

    private function filterData($data, AbstractServiceSingle $service) {
        if ($data === null) {
            return null;
        }
        $result = array();
        foreach ($service->getConnection()->getSupplementalDriver()->getColumns($service->getName()) as $column) {
            $name = $column['name'];
            if (array_key_exists($name, $data)) {
                $result[$name] = $data[$name];
            }
        }
        return $result;
    }

}

?>
