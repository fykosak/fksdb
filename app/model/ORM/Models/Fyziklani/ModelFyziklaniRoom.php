<?php

namespace FKSDB\ORM\Models\Fyziklani;
use FKSDB\ORM\AbstractModelSingle;

/**
 * Class FKSDB\ORM\Models\Fyziklani\ModelFyziklaniRoom
 *
 * @property integer room_id
 * @property string name
 * @property integer rows
 * @property integer columns
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
