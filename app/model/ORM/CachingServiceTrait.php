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
        unset($this->modelCache[$model->getPrimary(false)]);
        parent::updateModel($model, $data);
    }

    public function save(IModel &$model) {
        unset($this->modelCache[$model->getPrimary(false)]);
        parent::save($model);
    }

    public function dispose(IModel $model) {
        unset($this->modelCache[$model->getPrimary(false)]);
        parent::dispose($model);
    }

    public function findByPrimary($key) {
        if (!isset($this->modelCache[$key])) {
            $this->modelCache[$key] = parent::findByPrimary($key);
        }
        return $this->modelCache[$key];
    }

}

?>
