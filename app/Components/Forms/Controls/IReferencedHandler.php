<?php

namespace FKSDB\Components\Forms\Controls;

use Nette\ArrayHash;
use ORM\IModel;

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

    public function setResolution($resolution);

    public function update(IModel $model, ArrayHash $values, &$messages);

    public function createFromValues(ArrayHash $values, &$messages);

    public function isSecondaryKey($field);

    /**
     * @param string $field
     * @param mixed $key
     * @return IModel
     */
    public function findBySecondaryKey($field, $key);
}

