<?php

namespace FKSDB\ORM;

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
