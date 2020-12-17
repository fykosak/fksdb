<?php

namespace FKSDB\Components\Controls\Entity;

use FKSDB\Model\ORM\Models\AbstractModelSingle;

/**
 * Interface IEditEntityForm
 * @author Michal Červeňák <miso@fykos.cz>
 */
interface IEditEntityForm {
    public function setModel(AbstractModelSingle $model): void;
}
