<?php

namespace FKSDB\ORM\Services\StoredQuery;

use FKSDB\ORM\AbstractServiceSingle;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\Models\StoredQuery\ModelStoredQueryTag;
use FKSDB\ORM\Tables\TypedTableSelection;

/**
 * @author Lukáš Timko <lukast@fykos.cz>
 */
class ServiceStoredQueryTag extends AbstractServiceSingle {

    public function getModelClassName(): string {
        return ModelStoredQueryTag::class;
    }

    protected function getTableName(): string {
        return DbNames::TAB_STORED_QUERY_TAG;
    }

    /**
     * @param int|null $tagTypeId
     * @return TypedTableSelection|null
     */
    public function findByTagTypeId($tagTypeId) {
        if (!$tagTypeId) {
            return null;
        }
        return $this->getTable()->where('tag_type_id', $tagTypeId);
    }
}
