<?php

class ServiceBrawlRoom extends \AbstractServiceSingle {

    protected $tableName = \DbNames::TAB_BRAWL_ROOM;
    protected $modelClassName = 'ModelBrawlRoom';

    public function findByName($name) {
        return $this->getTable()->where('name', $name)->fetch();
    }
}
