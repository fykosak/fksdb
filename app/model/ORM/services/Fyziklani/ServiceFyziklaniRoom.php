<?php

use Nette\Database\Table\ActiveRow;

class ServiceFyziklaniRoom extends \AbstractServiceSingle {

    protected $tableName = \DbNames::TAB_FYZIKLANI_ROOM;
    protected $modelClassName = 'ModelFyziklaniRoom';

    public function findByName($name): ActiveRow {
        return $this->getTable()->where('name', $name)->fetch();
    }

    public function getRoomsByIds(array $ids): array {
        $rooms = [];
        foreach ($ids as $roomId) {
            /**
             * @var $room \ModelFyziklaniRoom
             */
            $room = ModelFyziklaniRoom::createFromTableRow($this->findByPrimary($roomId));
            if ($room) {
                $rooms[] = $room->__toArray();
            }
        }
        return $rooms;
    }
}
