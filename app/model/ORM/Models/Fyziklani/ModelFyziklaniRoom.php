<?php

namespace FKSDB\ORM\Models\Fyziklani;
use FKSDB\ORM\AbstractModelSingle;

/**
 * Class FKSDB\ORM\Models\Fyziklani\ModelFyziklaniRoom
 *
 * @property-read integer room_id
 * @property-read string name
 * @property-read integer rows
 * @property-read integer columns
 */
class ModelFyziklaniRoom extends AbstractModelSingle {

    /**
     * @return array
     */
    public function __toArray(): array {
        return [
            'roomId' => $this->room_id,
            'name' => $this->name,
            'x' => $this->columns,
            'y' => $this->rows,
        ];

    }
}
