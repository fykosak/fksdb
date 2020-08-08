<?php

namespace FKSDB\Components\Forms\Controls;

use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\IModel;
use Nette\Utils\ArrayHash;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
interface IReferencedHandler {

    const RESOLUTION_OVERWRITE = 'overwrite';
    const RESOLUTION_KEEP = 'keep';
    const RESOLUTION_EXCEPTION = 'exception';

    public function getResolution(): string;

    /**
     * @param string $resolution
     * @return void
     */
    public function setResolution(string $resolution);

    /**
     * @param IModel $model
     * @param ArrayHash $values
     * @return void
     */
    public function update(IModel $model, ArrayHash $values);

    /**
     * @param ArrayHash $values
     * @return AbstractModelSingle
     */
    public function createFromValues(ArrayHash $values);

    public function isSecondaryKey(string $field): bool;

    /**
     * @param string $field
     * @param string $key
     * @return IModel
     */
    public function findBySecondaryKey(string $field, string $key);
}
