<?php

namespace FKSDB\ORM\Services\Fyziklani;

use FKSDB\ORM\AbstractServiceSingle;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\Models\Fyziklani\ModelFyziklaniRoom;

/**
 * Class FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniRoom
 */
class ServiceFyziklaniRoom extends AbstractServiceSingle {
    /**
     * @return string
     */
    public function getModelClassName(): string {
        return ModelFyziklaniRoom::class;
    }

    /**
     * @return string
     */
    protected function getTableName(): string {
        return DbNames::TAB_FYZIKLANI_ROOM;
    }

    /**
     * @param array $ids
     * @return array
     */
    public function getRoomsByIds(array $ids): array {
        $rooms = [];
        foreach ($ids as $roomId) {
            /**
             * @var \FKSDB\ORM\Models\Fyziklani\ModelFyziklaniRoom $room
             */
            $room = ModelFyziklaniRoom::createFromActiveRow($this->findByPrimary($roomId));
            if ($room) {
                $rooms[] = $room->__toArray();
            }
        }
        return $rooms;
    }
}
