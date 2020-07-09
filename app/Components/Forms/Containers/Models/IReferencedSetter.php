<?php

namespace FKSDB\Components\Forms\Containers\Models;

use FKSDB\Components\Forms\Controls\ReferencedId;
use FKSDB\ORM\IModel;
use FKSDB\ORM\Models\ModelEvent;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
interface IReferencedSetter {

    /**
     * @param ReferencedContainer $container
     * @param IModel|null $model
     * @param string $mode
     * @param ModelEvent|null $event
     * @return void
     */
    public function setModel(ReferencedContainer $container, IModel $model = null, string $mode = ReferencedId::MODE_NORMAL,$event=null);
}
