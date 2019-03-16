<?php

/**
 * @author Lukáš Timko <lukast@fykos.cz>
 */
class ServiceStoredQueryTag extends AbstractServiceSingle {

    protected $tableName = DbNames::TAB_STORED_QUERY_TAG;
    protected $modelClassName = 'FKSDB\ORM\Models\ModelStoredQueryTag';

    /**
     * @param int|null $tagTypeId
     * @return Nette\Database\Table\Selection|null
     */
    public function findByTagTypeId($tagTypeId) {
        if (!$tagTypeId) {
            return null;
        }
        $result = $this->getTable()->where('tag_type_id', $tagTypeId);
        return $result ? : null;
    }
}
