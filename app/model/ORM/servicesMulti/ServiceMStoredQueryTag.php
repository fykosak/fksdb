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
}