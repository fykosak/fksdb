<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models\Fyziklani;

use Fykosak\NetteORM\AbstractModel;

/**
 * @property-read int event_id
 * @property-read \DateTimeInterface game_start
 * @property-read \DateTimeInterface game_end
 * @property-read \DateTimeInterface result_display
 * @property-read \DateTimeInterface result_hide
 * @property-read int refresh_delay
 * @property-read bool result_hard_display
 * @property-read int tasks_on_board
 * @property-read string available_points
 */
class ModelFyziklaniGameSetup extends AbstractModel
{
    /**
     * @return int[]
     */
    public function getAvailablePoints(): array
    {
        return \array_map(fn(string $value): int => +trim($value), \explode(',', $this->available_points));
    }

    /**
     * @note Take care, this function is not state-less!!!
     */
    public function isResultsVisible(): bool
    {
        if ($this->result_hard_display) {
            return true;
        }
        $before = (time() < strtotime($this->result_hide->format('c')));
        $after = (time() > strtotime($this->result_display->format('c')));
        return ($before && $after);
    }
}
