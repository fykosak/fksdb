<?php

namespace FKSDB\ORM\Models\Fyziklani;

use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\DeprecatedLazyModel;

/**
 * Class FKSDB\ORM\Models\Fyziklani\ModelFyziklaniRoom
 *
 * @property-read int room_id
 * @property-read string name
 * @property-read int rows
 * @property-read int columns
 */
class ModelFyziklaniRoom extends AbstractModelSingle {
    use DeprecatedLazyModel;

    public function __toArray(): array {
        return [
            'roomId' => $this->room_id,
            'name' => $this->name,
            'x' => $this->columns,
            'y' => $this->rows,
        ];
    }
}
