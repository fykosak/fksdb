<?php

namespace FKSDB\ORM\Models\Fyziklani;
use FKSDB\ORM\AbstractModelSingle;

/**
 * Class FKSDB\ORM\Models\Fyziklani\ModelFyziklaniRoom
 *
 * @property-readinteger room_id
 * @property-readstring name
 * @property-readinteger rows
 * @property-readinteger columns
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
