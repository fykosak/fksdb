<?php

namespace FKSDB\Models\ORM\Services\Fyziklani;



use FKSDB\Models\ORM\DbNames;
use FKSDB\Models\ORM\Models\Fyziklani\ModelFyziklaniRoom;
use FKSDB\Models\ORM\Services\AbstractServiceSingle;
use Nette\Database\Context;
use Nette\Database\IConventions;

/**
 * Class ServiceFyziklaniRoom
 * @author Michal Červeňák <miso@fykos.cz>
 */
class ServiceFyziklaniRoom extends AbstractServiceSingle {


    public function __construct(Context $connection, IConventions $conventions) {
        parent::__construct($connection, $conventions, DbNames::TAB_FYZIKLANI_ROOM, ModelFyziklaniRoom::class);
    }

    /**
     * @param array $ids
     * @return ModelFyziklaniRoom[]
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
