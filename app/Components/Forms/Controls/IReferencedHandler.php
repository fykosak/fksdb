<?php

namespace FKSDB\Components\Forms\Controls;

use FKSDB\ORM\IModel;
use Nette\ArrayHash;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
interface IReferencedHandler {

    const RESOLUTION_OVERWRITE = 'overwrite';
    const RESOLUTION_KEEP = 'keep';
    const RESOLUTION_EXCEPTION = 'exception';

    public function getResolution();

    /**
     * @param $resolution
     * @return mixed
     */
    public function setResolution($resolution);

    /**
     * @param IModel $model
     * @param ArrayHash $values
     * @return mixed
     */
    public function update(IModel $model, ArrayHash $values);

    /**
     * @param ArrayHash $values
     * @return mixed
     */
    public function createFromValues(ArrayHash $values);

    /**
     * @param $field
     * @return mixed
     */
    public function isSecondaryKey($field);

    /**
     * @param string $field
     * @param mixed $key
     * @return IModel
     */
    public function findBySecondaryKey($field, $key);
}
