<?php

/**
 * Class ServiceFyziklaniRoom
 */
class ServiceFyziklaniRoom extends \AbstractServiceSingle {

    protected $tableName = \DbNames::TAB_FYZIKLANI_ROOM;
    protected $modelClassName = 'ModelFyziklaniRoom';

    /**
     * @param array $ids
     * @return array
     */
    public function getRoomsByIds(array $ids): array {
        $rooms = [];
        foreach ($ids as $roomId) {
            /**
             * @var \ModelFyziklaniRoom $room
             */
            $room = ModelFyziklaniRoom::createFromTableRow($this->findByPrimary($roomId));
            if ($room) {
                $rooms[] = $room->__toArray();
            }
        }
        return $rooms;
    }
}
