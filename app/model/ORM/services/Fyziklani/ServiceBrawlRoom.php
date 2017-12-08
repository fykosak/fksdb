<?php

class ServiceBrawlRoom extends \AbstractServiceSingle {

    protected $tableName = \DbNames::TAB_BRAWL_ROOM;
    protected $modelClassName = 'ModelBrawlRoom';

    public function findByName($name) {
        return $this->getTable()->where('name', $name)->fetch();
    }
    public function getRoomsById(array $ids){
        $rooms = [];
        foreach ($ids as $roomId) {
            /**
             * @var $room \ModelBrawlRoom
             */
            $room = $this->findByPrimary($roomId);
            if ($room) {
                $rooms[] = [
                    'roomId' => $room->room_id,
                    'name' => $room->name,
                    'x' => $room->columns,
                    'y' => $room->rows,
                ];
            }
        }
        return $rooms;
    }
}
