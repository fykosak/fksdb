<?php

namespace FKSDB\Models\ORM\Services\Fyziklani;

use FKSDB\Models\ORM\Models\Fyziklani\ModelFyziklaniRoom;
use Fykosak\NetteORM\AbstractService;

/**
 * Class ServiceFyziklaniRoom
 * @author Michal Červeňák <miso@fykos.cz>
 */
class ServiceFyziklaniRoom extends AbstractService {

    /**
     * @param array $ids
     * @return array[]
     */
    public function getRoomsByIds(array $ids): array {
        $rooms = [];
        foreach ($ids as $roomId) {
            /** @var ModelFyziklaniRoom $room */
            $room = $this->findByPrimary($roomId);
            if ($room) {
                $rooms[] = $room->__toArray();
            }
        }
        return $rooms;
    }
}
