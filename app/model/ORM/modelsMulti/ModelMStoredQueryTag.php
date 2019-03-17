<?php

use FKSDB\ORM\Models\StoredQuery\ModelStoredQueryTag;
use FKSDB\ORM\Models\StoredQuery\ModelStoredQueryTagType;

/**
 *
 * @author Lukáš Timko <lukast@fykos.cz>
 */
class ModelMStoredQueryTag extends AbstractModelMulti {

    /**
     * @return ModelStoredQueryTagType
     */
    public function getStoredQueryTagType() {
        return $this->getMainModel();
    }

    /**
     * @return \FKSDB\ORM\Models\StoredQuery\ModelStoredQueryTag
     */
    public function getStoredQueryTag() {
        return $this->getJoinedModel();
    }

}
