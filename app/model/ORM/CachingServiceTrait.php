<?php

namespace ORM;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
trait CachingServiceTrait {

    private $modelCache = array();

    public function updateModel(IModel $model, $data) {
        unset($this->modelCache[$this->getKey($model->getPrimary(false))]);
        parent::updateModel($model, $data);
    }

    public function save(IModel &$model) {
        unset($this->modelCache[$this->getKey($model->getPrimary(false))]);
        parent::save($model);
    }

    public function dispose(IModel $model) {
        unset($this->modelCache[$this->getKey($model->getPrimary(false))]);
        parent::dispose($model);
    }

    public function findByPrimary($key) {
        $cacheKey = $this->getKey($key);
        if (!isset($this->modelCache[$cacheKey])) {
            $this->modelCache[$cacheKey] = parent::findByPrimary($key);
        }
        return $this->modelCache[$cacheKey];
    }

    public function preloadCache($where) {
        foreach ($this->getTable()->where($where) as $model) {
            $cacheKey = $this->getKey($model->getPrimary());
            $this->modelCache[$cacheKey] = $model;
        }
    }

    public function clearCache() {
        $this->modelCache = array();
    }

    private function getKey($key) {
        if (is_array($key)) {
            return implode('-', $key);
        } else {
            return $key;
        }
    }

}

?>
