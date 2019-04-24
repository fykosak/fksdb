<?php

use FKSDB\ORM\AbstractModelMulti;
use FKSDB\ORM\AbstractServiceMulti;
use FKSDB\ORM\Services\StoredQuery\ServiceStoredQueryTag;
use FKSDB\ORM\Services\StoredQuery\ServiceStoredQueryTagType;

/**
 * @author Lukáš Timko <lukast@fykos.cz>
 * @deprecated
 */
class ServiceMStoredQueryTag extends AbstractServiceMulti {

    protected $modelClassName = 'ModelMStoredQueryTag';
    protected $joiningColumn = 'tag_type_id';

    /**
     * ServiceMStoredQueryTag constructor.
     * @param ServiceStoredQueryTagType $mainService
     * @param ServiceStoredQueryTag $joinedService
     */
    public function __construct(ServiceStoredQueryTagType $mainService, ServiceStoredQueryTag $joinedService) {
        parent::__construct($mainService, $joinedService);
    }

    /**
     * @param null $data
     * @return AbstractModelMulti|ModelMStoredQueryTag
     */
    public function createNew($data = null) {
        $mainModel = $this->getMainService()->findByPrimary($data['tag_type_id']);
        if ($mainModel === null) {
            throw new \Nette\InvalidArgumentException;
        }
        $joinedModel = $this->getJoinedService()->createNew($data);

        $result = new ModelMStoredQueryTag($this, $mainModel, $joinedModel);
        return $result;
    }
}
