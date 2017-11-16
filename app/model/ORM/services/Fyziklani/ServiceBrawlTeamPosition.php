<?php

class ServiceBrawlTeamPosition extends \AbstractServiceSingle {

    protected $tableName = \DbNames::TAB_BRAWL_TEAM_POSITION;
    protected $modelClassName = 'ModelBrawlTeamPosition';

    public function findByTeamId($id) {
        /**
         * @var $model \ModelBrawlTeamPosition
         */
        return $this->getTable()->where('e_fyziklani_team_id', $id)->fetch();
    }
}
