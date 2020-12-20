<?php

namespace FKSDB\Models\ORM\Services\StoredQuery;

use FKSDB\Models\ORM\DbNames;
use FKSDB\Models\ORM\Models\StoredQuery\ModelStoredQueryTag;
use FKSDB\Models\ORM\Services\AbstractServiceSingle;
use FKSDB\Models\ORM\Tables\TypedTableSelection;
use Nette\Database\Context;
use Nette\Database\IConventions;

/**
 * @author Lukáš Timko <lukast@fykos.cz>
 */
class ServiceStoredQueryTag extends AbstractServiceSingle {

    public function __construct(Context $connection, IConventions $conventions) {
        parent::__construct($connection, $conventions, DbNames::TAB_STORED_QUERY_TAG, ModelStoredQueryTag::class);
    }

    /**
     * @param int|null $tagTypeId
     * @return TypedTableSelection|null
     */
    public function findByTagTypeId($tagTypeId): ?TypedTableSelection {
        if (!$tagTypeId) {
            return null;
        }
        return $this->getTable()->where('tag_type_id', $tagTypeId);
    }
}
