<?php

namespace FKSDB\Components\Controls\Entity;

use FKSDB\ORM\AbstractModelSingle;

/**
 * Interface IEditEntityForm
 * @package FKSDB\Components\Controls\Entity
 */
interface IEditEntityForm {
    /**
     * @param AbstractModelSingle $model
     * @return void
     */
    public function setModel(AbstractModelSingle $model);
}
