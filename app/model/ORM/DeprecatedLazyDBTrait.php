<?php

namespace FKSDB\ORM;

use Nette\DeprecatedException;

/**
 * Trait DeprecatedLazyDBTrait
 * @author Michal Červeňák <miso@fykos.cz>
 * Use for IService that is lazy DB access not supported
 */
trait DeprecatedLazyDBTrait {
    /**
     * @param null $data
     * @return void
     */
    public function createNew($data = null) {
        throw new DeprecatedException();
    }

    /**
     * @param IModel $model
     * @return void
     * @deprecated
     */
    public function save(IModel &$model) {
        throw new DeprecatedException();
    }

    /**
     * @param IModel $model
     * @param $data
     * @param bool $alive
     * @return mixed
     * @deprecated
     */
    public function updateModel(IModel $model, $data, $alive = true) {
        throw new DeprecatedException();
    }
}
