<?php

namespace FKSDB\ORM\Models\Fyziklani;

use FKSDB\ORM\AbstractModelSingle;
use Nette\Utils\DateTime;

/**
 * Class ModelFyziklaniGameSetup
 * @package FKSDB\ORM\Models\Fyziklani
 * @property-read int event_id
 * @property-read DateTime game_start
 * @property-read DateTime game_end
 * @property-read DateTime result_display
 * @property-read DateTime result_hide
 * @property-read int refresh_delay
 * @property-read bool result_hard_display
 * @property-read int tasks_on_board
 * @property-read string available_points
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

    /**
     * @return bool
     */
    public function isResultsVisible(): bool {
        if ($this->result_hard_display) {
            return true;
        }
        $before = (time() < strtotime($this->result_hide));
        $after = (time() > strtotime($this->result_display));
        return ($before && $after);
    }
}
