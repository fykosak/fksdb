<?php
namespace FKSDB\ORM\ModelsMulti;

use FKSDB\ORM\AbstractModelMulti;
use FKSDB\ORM\IModel;
use FKSDB\ORM\Models\ModelFlag;
use FKSDB\ORM\Models\ModelPersonHasFlag;

/**
 *
 * @author Lukáš Timko <lukast@fykos.cz>
 */
class ModelMPersonHasFlag extends AbstractModelMulti {

    /**
     * @return IModel|ModelFlag
     */
    public function getFlag() {
        return $this->getMainModel();
    }

    /**
     * @return IModel|ModelPersonHasFlag
     */
    public function getPersonHasFlag() {
        return $this->getJoinedModel();
    }

}
