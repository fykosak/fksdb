<?php

namespace FKSDB\Components\Forms\Containers\Models;

use FKSDB\ORM\IModel;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
interface IReferencedSetter {

    public const MODE_NORMAL = 'MODE_NORMAL';
    public const MODE_FORCE = 'MODE_FORCE';
    public const MODE_ROLLBACK = 'MODE_ROLLBACK';

    public function setModel(ReferencedContainer $container, IModel $model = null, string $mode = self::MODE_NORMAL): void;
}
