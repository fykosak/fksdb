<?php

namespace FKSDB\Components\Controls\Entity;

use FKSDB\ORM\AbstractModelSingle;

/**
 * Interface IEditEntityForm
 * @author Michal Červeňák <miso@fykos.cz>
 */
interface IEditEntityForm {
    /**
     * @param AbstractModelSingle $model
     * @return void
     */
    public function setModel(AbstractModelSingle $model);
}
