<?php

use FKSDB\ORM\AbstractModelMulti;
use FKSDB\ORM\Models\StoredQuery\ModelStoredQueryTagType;

/**
 *
 * @author Lukáš Timko <lukast@fykos.cz>
 */
class ModelMStoredQueryTag extends AbstractModelMulti {

    /**
     * @return \FKSDB\ORM\IModel|ModelStoredQueryTagType
     */
    public function getStoredQueryTagType() {
        return $this->getMainModel();
    }

    /**
     * @return \FKSDB\ORM\IModel|\FKSDB\ORM\Models\StoredQuery\ModelStoredQueryTag
     */
    public function getStoredQueryTag() {
        return $this->getJoinedModel();
    }

}
