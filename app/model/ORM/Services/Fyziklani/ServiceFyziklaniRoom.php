<?php

namespace FKSDB\ORM\Services\Fyziklani;

use FKSDB\ORM\AbstractServiceSingle;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\DeprecatedLazyDBTrait;
use FKSDB\ORM\Models\Fyziklani\ModelFyziklaniRoom;

/**
 * Class ServiceFyziklaniRoom
 * @author Michal Červeňák <miso@fykos.cz>
 */
class ServiceFyziklaniRoom extends AbstractServiceSingle {
    use DeprecatedLazyDBTrait;

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
