<?php

namespace FKSDB\ORM\Models\Fyziklani;

use FKSDB\ORM\AbstractModelSingle;
use Nette\Security\IResource;
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
class ModelFyziklaniGameSetup extends AbstractModelSingle implements IResource {
    const RESOURCE_ID = 'fyziklani.gameSetup';

    /**
     * @return int[]
     */
    public function getAvailablePoints(): array {
        return \array_map(function (string $value): int {
            return +trim($value);
        }, \explode(',', $this->available_points));
    }

    /**
     * @return bool
     * Take cate, this function is not state-less!!!
     */
    public function isResultsVisible(): bool {
        if ($this->result_hard_display) {
            return true;
        }
        $before = (time() < strtotime($this->result_hide));
        $after = (time() > strtotime($this->result_display));
        return ($before && $after);
    }

    /**
     * @inheritDoc
     */
    function getResourceId() {
        return self::RESOURCE_ID;
    }
}
