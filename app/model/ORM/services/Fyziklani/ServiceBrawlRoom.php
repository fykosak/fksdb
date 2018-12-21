<?php

use Nette\Database\Table\ActiveRow;

class ServiceBrawlRoom extends \AbstractServiceSingle {

    protected $tableName = \DbNames::TAB_BRAWL_ROOM;
    protected $modelClassName = 'ModelBrawlRoom';

    public function findByName($name): ActiveRow {
        return $this->getTable()->where('name', $name)->fetch();
    }

    public function getRoomsByIds(array $ids): array {
        $rooms = [];
        foreach ($ids as $roomId) {
            /**
             * @var $room \ModelBrawlRoom
             */
            $room = ModelBrawlRoom::createFromTableRow($this->findByPrimary($roomId));
            if ($room) {
                $rooms[] = $room->__toArray();
            }
        }
        return $rooms;
    }
}
