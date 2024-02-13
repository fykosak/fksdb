<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models\Fyziklani;

use Fykosak\NetteORM\Model\Model;

/**
 * @property-read int $event_id
 * @property-read \DateTimeInterface $game_start
 * @property-read \DateTimeInterface $game_end
 * @property-read \DateTimeInterface $result_display
 * @property-read \DateTimeInterface $result_hide
 * @property-read int $refresh_delay
 * @property-read int $result_hard_display
 * @property-read int $tasks_on_board
 * @property-read string $available_points
 */
final class GameSetupModel extends Model
{
    /**
     * @phpstan-return int[]
     */
    public function getAvailablePoints(): array
    {
        return $this->available_points ? \array_map(
            fn(string $value): int => (int)trim($value),
            \explode(',', $this->available_points)
        )
        : [];
    }

    /**
     * @note Take care, this function is not state-less!!!
     */
    public function isResultsVisible(): bool
    {
        if ($this->result_hard_display) {
            return true;
        }
        $before = (time() < $this->result_hide->getTimestamp());
        $after = (time() > $this->result_display->getTimestamp());
        return ($before && $after);
    }

    /**
     * @note Check if current time is in between the midnight before game_start
     * and midnight after game_end.
     */
    public function isGameTimeRange(): bool
    {
        $startMidnight = new \DateTime($this->game_start->format("Y-m-d"));
        $afterStartMidnight = ($startMidnight->getTimestamp() < time());
        $endMidnight = \DateTime::createFromInterface($this->game_end)->add(new \DateInterval("P1D"));
        $beforeEndMidnight = (time() < $endMidnight->getTimestamp());
        return ($afterStartMidnight && $beforeEndMidnight);
    }
}
