<?php

namespace FKSDB\ORM;

use Nette\Database\Table\Selection;
use Traversable;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
interface IService {

    /**
     * @param array|Traversable $data
     */
    public function createNew($data = null);

    /**
     * @param array|Traversable $data
     * @return IModel
     */
    public function createNewModel($data);

    /**
     * @param $key
     * @return mixed
     */
    public function findByPrimary($key);

    /**
     * @param $key
     * @return IModel|null
     */
    public function findByPrimary2(int $key);

    /**
     * @param IModel $model
     * @return mixed
     * @deprecated
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

    /**
<<<<<<< HEAD
     * @param IModel $model
     * @param $data
     * @return mixed
     */
    public function updateModel2(IModel $model, $data);

    /**
     * @param IModel $model
     * @param $data
     * @return mixed
     * @deprecated
     */
    public function updateModel(IModel $model, $data);
    /**
     * @return string
     */
    public function getModelClassName(): string;
}
