<?php

namespace FKSDB\ORM\Services\Fyziklani;

use FKSDB\ORM\AbstractServiceSingle;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\Models\Fyziklani\ModelFyziklaniRoom;

/**
 * Class FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniRoom
 */
class ServiceFyziklaniRoom extends AbstractServiceSingle {

    public function getModelClassName(): string {
        return ModelFyziklaniRoom::class;
    }

    protected function getTableName(): string {
        return DbNames::TAB_FYZIKLANI_ROOM;
    }

    /**
     * @param array $ids
     * @return ModelFyziklaniRoom[]
     */
    public function getRoomsByIds(array $ids): array {
        $rooms = [];
        foreach ($ids as $roomId) {
            $room = ModelFyziklaniRoom::createFromActiveRow($this->findByPrimary($roomId));
            if ($room) {
                $rooms[] = $room->__toArray();
            }
        }
        return $rooms;
    }
}
