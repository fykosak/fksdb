<?php

namespace FKSDB\ORM\ModelsMulti;

use FKSDB\ORM\AbstractModelMulti;
use FKSDB\ORM\IModel;
use FKSDB\ORM\Models\StoredQuery\ModelStoredQueryTag;
use FKSDB\ORM\Models\StoredQuery\ModelStoredQueryTagType;

/**
 *
 * @author Lukáš Timko <lukast@fykos.cz>
 */
class ModelMStoredQueryTag extends AbstractModelMulti {

    /**
     * @return IModel|ModelStoredQueryTagType
     */
    public function getStoredQueryTagType(): ModelStoredQueryTagType {
        return $this->getMainModel();
    }

    /**
     * @return IModel|ModelStoredQueryTag
     */
    public function getStoredQueryTag(): ModelStoredQueryTag {
        return $this->getJoinedModel();
    }
}
