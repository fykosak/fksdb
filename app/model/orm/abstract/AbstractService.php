<?php

/**
 * Service class to high-level manipulation with ORM objects.
 * Use singleton descedants implemetations.
 * 
 * @note Because of compatibility with PHP 5.2 (no LSB), part of the code has to be
 *       duplicated in all descedant classes.
 * 
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
abstract class AbstractService extends NTableSelection {

    /**
     * @var string
     */
    protected $modelClassName;

    /**
     * @var array of AbstractService  singleton instances of descedants
     */
    protected static $instances = array();

    /**
     * Use this method to create new models!
     * 
     * @param array $data
     * @return AbstractModel
     */
    public function createNew($data = null) {
        $className = $this->modelClassName;
        if($data === null){
            $data = $this->getDefaultData();
        }
        return new $className($data, $this);
    }

    /**
     * Syntactic sugar.
     * 
     * @param int $key
     * @return NTableRow|null
     */
    public function findByPrimary($key) {
        $result = $this->find($key)->fetch();
        if ($result !== false) {
            return $result;
        } else {
            return null;
        }
    }

    /**
     * Use this method to store model!
     * 
     * @param NTableRow $model
     * @throws InvalidArgumentException
     * @throws InvalidStateException
     */
    public function save(NTableRow & $model) {
        if (!$model instanceof $this->modelClassName) {
            throw new InvalidArgumentException('Service for class ' . $this->modelClassName . ' cannot store ' . get_class($model));
        }
        if (!isset($model[$this->getPrimary()])) { // new model
            $result = $this->insert($model->toArray());
            if ($result !== false) {
                $model = $result;
            } else {
                throw new InvalidStateException('Error when storing a model.'); //TODO expressive description
            }
        } else {
            if($model->update() === false){
                throw new InvalidStateException('Error when storing a model.'); //TODO expressive description
            }
        }
    }
    
    /**
     * Default data for the new model.
     * 
     * @return array
     */
    protected function getDefaultData(){
        return array();
    }

    /**
     * This override ensures returned objects are of correct class.
     * 
     * @param array $row
     * @return \className
     */
    protected function createRow(array $row) {
        $className = $this->modelClassName;
        return new $className($row, $this);
    }

}

?>
