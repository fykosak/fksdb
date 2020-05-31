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
     * @param iterable $data
     */
    public function createNew($data = null);

    public function createNewModel(iterable $data): IModel;

    /**
     * @param int $key
     * @return AbstractModelSingle|AbstractModelMulti|IModel
     */
    public function findByPrimary($key): ?IModel;

    /**
     * @param IModel $model
     * @return void
     * @deprecated
     */
    public function save(IModel &$model);

    /**
     * @param IModel $model
     * @return void
     */
    public function dispose(IModel $model);

    /**
     * @return Selection
     */
    public function getTable();

    /**
     * @param IModel $model
     * @param $data
     * @return mixed
     * @deprecated
     */
    public function updateModel(IModel $model, $data);

    /**
     * @return string|AbstractModelSingle|AbstractModelMulti
     */
    public function getModelClassName(): string;
}
