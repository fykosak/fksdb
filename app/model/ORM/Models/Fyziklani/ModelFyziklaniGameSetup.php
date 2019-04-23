<?php

namespace FKSDB\ORM\Models\Fyziklani;

use FKSDB\ORM\AbstractModelSingle;
use Nette\Utils\DateTime;

/**
 * Class ModelFyziklaniGameSetup
 * @package FKSDB\ORM\Models\Fyziklani
 * @property-readint event_id
 * @property-readDateTime game_start
 * @property-readDateTime game_end
 * @property-readDateTime result_display
 * @property-readDateTime result_hide
 * @property-readint refresh_delay
 * @property-readbool result_hard_display
 * @property-readint tasks_on_board
 * @property-readstring available_points
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
