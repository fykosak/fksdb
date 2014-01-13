<?php

namespace ORM;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
interface IService {

    /**
     * @param IModel $data
     */
    public function createNew($data = null);

    public function findByPrimary($key);

    public function updateModel(IModel $model, $data);

    public function save(IModel &$model);

    public function dispose(IModel $model);
}
