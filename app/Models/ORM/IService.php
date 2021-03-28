<?php

namespace FKSDB\Models\ORM;

use Fykosak\NetteORM\AbstractModel;
use FKSDB\Models\ORM\ModelsMulti\AbstractModelMulti;
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

    /**
     * @param ActiveRow $model
     * @return void
     * @deprecated
     */
    public function save(ActiveRow &$model): void;

    public function getTable(): Selection;

    /**
     * @param ActiveRow $model
     * @param iterable $data
     * @return void
     * @deprecated
     */
    public function updateModelLegacy(ActiveRow $model, iterable $data): void;

    /**
     * @return string|AbstractModel|AbstractModelMulti
     */
    public function getModelClassName(): string;
}
