<?php

namespace FKSDB\Model\ORM\Models\Fyziklani;
use FKSDB\Model\ORM\Models\AbstractModelSingle;

/**
 * Class FKSDB\Model\ORM\Models\Fyziklani\ModelFyziklaniRoom
 *
 * @property-read int room_id
 * @property-read string name
 * @property-read int rows
 * @property-read int columns
 */
class ModelFyziklaniRoom extends AbstractModelSingle {

    public function __toArray(): array {
        return [
            'roomId' => $this->room_id,
            'name' => $this->name,
            'x' => $this->columns,
            'y' => $this->rows,
        ];
    }
}
