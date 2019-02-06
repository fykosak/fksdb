<?php

namespace ORM;

use Nette\Database\Table\Selection;

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

    /**
     * @param $key
     * @return mixed
     */
    public function findByPrimary($key);

    /**
     * @param IModel $model
     * @param $data
     * @param bool $alive
     * @return mixed
     */
    public function updateModel(IModel $model, $data, $alive = true);

    /**
     * @param IModel $model
     * @return mixed
     */
    public function save(IModel &$model);

    /**
     * @param IModel $model
     * @return mixed
     */
    public function dispose(IModel $model);

    /**
     * @return Selection
     */
    public function getTable();
}
