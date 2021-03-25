<?php

namespace FKSDB\Models\ORM;

use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\Selection;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
interface IService {

    /**
     * @param iterable|null $data
     * @deprecated
     */
    public function createNew(?iterable $data = null);

    /**
     * @param mixed $key
     * @return ActiveRow|null
     */
    public function findByPrimary($key);

    public function getTable(): Selection;

    /**
     * @param ActiveRow $model
     * @param iterable $data
     * @return void
     * @deprecated
     */
    public function updateModel(ActiveRow $model, iterable $data): void;
}
