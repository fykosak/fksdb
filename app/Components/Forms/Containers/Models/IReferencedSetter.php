<?php

namespace FKSDB\Components\Forms\Containers\Models;

use ORM\IModel;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
interface IReferencedSetter {

    const MODE_NORMAL = 'MODE_NORMAL';
    const MODE_FORCE = 'MODE_FORCE';
    const MODE_ROLLBACK = 'MODE_ROLLBACK';

    /**
     * @param ReferencedContainer $container
     * @param IModel|null $model
     * @param string $mode
     * @return mixed
     */
    public function setModel(ReferencedContainer $container, IModel $model = null, $mode = self::MODE_NORMAL);
}
