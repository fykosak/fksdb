<?php

namespace FKSDB\Model\ORM;

use Nette\DeprecatedException;

/**
 * Trait DeprecatedLazyDBTrait
 * @author Michal Červeňák <miso@fykos.cz>
 * Use for IService that is lazy DB access not supported
 */
trait DeprecatedLazyDBTrait {
    /**
     * @param iterable|null $data
     * @return IModel|mixed
     * @deprecated
     */
    public function createNew(?iterable $data = null) {
        throw new DeprecatedException();
    }

    /**
     * @param IModel $model
     * @return void
     * @deprecated
     */
    public function save(IModel &$model): void {
        throw new DeprecatedException();
    }

    /**
     * @param IModel $model
     * @param iterable $data
     * @param bool $alive
     * @return mixed
     * @deprecated
     */
    public function updateModel(IModel $model, iterable $data, bool $alive = true): void {
        throw new DeprecatedException();
    }
}
