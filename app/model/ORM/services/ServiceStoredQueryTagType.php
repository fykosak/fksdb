<?php

/**
 * @author Lukáš Timko <lukast@fykos.cz>
 */
class ServiceStoredQueryTagType extends AbstractServiceSingle {

    protected $tableName = DbNames::TAB_STORED_QUERY_TAG_TYPE;
    protected $modelClassName = 'FKSDB\ORM\ModelStoredQueryTagType';

}
