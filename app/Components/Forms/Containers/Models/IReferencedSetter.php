<?php

namespace FKSDB\Components\Forms\Containers\Models;

use ORM\IModel;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
interface IReferencedSetter {

    const MODE_NORMAL = 0;
    const MODE_FORCE = 1;
    const MODE_ROLLBACK = 2;

    public function setModel(ReferencedContainer $container, IModel $model = null, $mode = self::MODE_NORMAL);
}
