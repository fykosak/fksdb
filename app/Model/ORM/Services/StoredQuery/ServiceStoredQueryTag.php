<?php

namespace FKSDB\Model\ORM\Services\StoredQuery;

use FKSDB\Model\ORM\DbNames;
use FKSDB\Model\ORM\Models\StoredQuery\ModelStoredQueryTag;
use FKSDB\Model\ORM\Services\AbstractServiceSingle;
use Fykosak\Utils\ORM\TypedTableSelection;
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
