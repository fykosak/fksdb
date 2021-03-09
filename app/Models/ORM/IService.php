<?php

namespace FKSDB\Models\ORM;

use Fykosak\NetteORM\AbstractModel;
use FKSDB\Models\ORM\ModelsMulti\AbstractModelMulti;
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
     * @return IModel|null
     */
    public function findByPrimary($key);

    /**
     * @param IModel $model
     * @return void
     * @deprecated
     */
    public function save(IModel &$model): void;

    public function getTable(): Selection;

    /**
     * @param IModel $model
     * @param iterable $data
     * @return void
     * @deprecated
     */
    public function updateModel(IModel $model, iterable $data): void;

    /**
     * @return string|AbstractModel|AbstractModelMulti
     */
    public function getModelClassName(): string;
}
