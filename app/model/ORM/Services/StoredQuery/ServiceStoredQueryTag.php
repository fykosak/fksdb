<?php

namespace FKSDB\ORM\Services\StoredQuery;

use FKSDB\ORM\AbstractServiceSingle;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\Models\StoredQuery\ModelStoredQueryTag;
use Nette;

/**
 * @author Lukáš Timko <lukast@fykos.cz>
 */
class ServiceStoredQueryTag extends AbstractServiceSingle {

    /**
     * @return string
     */
    public function getModelClassName(): string {
        return ModelStoredQueryTag::class;
    }

    /**
     * @return string
     */
    protected function getTableName(): string {
        return DbNames::TAB_STORED_QUERY_TAG;
    }

    /**
     * @param int|null $tagTypeId
     * @return Nette\Database\Table\Selection|null
     */
    public function findByTagTypeId($tagTypeId) {
        if (!$tagTypeId) {
            return null;
        }
        $result = $this->getTable()->where('tag_type_id', $tagTypeId);
        return $result ?: null;
    }
}
