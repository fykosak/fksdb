<?php

/**
 * @author Lukáš Timko <lukast@fykos.cz>
 */
class ServiceMStoredQueryTag extends AbstractServiceMulti {

    protected $modelClassName = 'ModelMStoredQueryTag';
    protected $joiningColumn = 'tag_type_id';

    public function __construct(ServiceStoredQueryTagType $mainService, ServiceStoredQueryTag $joinedService) {
        parent::__construct($mainService, $joinedService);
    }

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
