<?php

use FKSDB\ORM\Models\ModelFlag;
use FKSDB\ORM\Models\ModelPersonHasFlag;

/**
 *
 * @author Lukáš Timko <lukast@fykos.cz>
 */
class ModelMPersonHasFlag extends AbstractModelMulti {

    /**
     * @return \FKSDB\ORM\Models\ModelFlag
     */
    public function getFlag() {
        return $this->getMainModel();
    }

    /**
     * @return \FKSDB\ORM\Models\ModelPersonHasFlag
     */
    public function getPersonHasFlag() {
        return $this->getJoinedModel();
    }

}
