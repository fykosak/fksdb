<?php

namespace FKSDB\ORM\Services\Fyziklani;

use FKSDB\ORM\AbstractServiceSingle;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\Models\Fyziklani\ModelFyziklaniRoom;

/**
 * Class FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniRoom
 */
class ServiceFyziklaniRoom extends AbstractServiceSingle {

    protected $tableName = DbNames::TAB_FYZIKLANI_ROOM;
    protected $modelClassName = 'FKSDB\ORM\Models\Fyziklani\ModelFyziklaniRoom';

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
            $room = ModelFyziklaniRoom::createFromTableRow($this->findByPrimary($roomId));
            if ($room) {
                $rooms[] = $room->__toArray();
            }
        }
        return $rooms;
    }
}
