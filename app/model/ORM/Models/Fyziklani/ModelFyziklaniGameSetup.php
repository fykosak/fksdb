<?php

namespace FKSDB\ORM\Models\Fyziklani;

use FKSDB\ORM\AbstractModelSingle;
use Nette\Utils\DateTime;

/**
 * Class ModelFyziklaniGameSetup
 * @package FKSDB\ORM\Models\Fyziklani
 * @property int event_id
 * @property DateTime game_start
 * @property DateTime game_end
 * @property DateTime result_display
 * @property DateTime result_hide
 * @property int refresh_delay
 * @property bool result_hard_display
 * @property int tasks_on_board
 * @property string available_points
 */
class ModelFyziklaniGameSetup extends AbstractModelSingle {
    /**
     * @return array
     */
    public function getAvailablePoints(): array {
        return \array_map(function ($value) {
            return trim($value);
        }, \explode(',', $this->available_points));
    }
}
