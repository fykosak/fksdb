<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models\Fyziklani;

use Fykosak\NetteORM\Model\Model;
use Nette\Utils\DateTime;

/**
 * @property-read int $event_id
 * @property-read DateTime $game_start
 * @property-read DateTime $game_end
 * @property-read DateTime $result_display
 * @property-read DateTime $result_hide
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
            fn (string $value): int => (int)trim($value),
            \explode(',', $this->available_points)
        ) : [];
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
     * @throws \Exception
     */
    public function isGameTimeRange(): bool
    {
        $startMidnight = new \DateTimeImmutable($this->game_start->format('Y-m-d'));
        $afterStartMidnight = ($startMidnight->getTimestamp() < time());

        $endMidnight = $this->game_end->modifyClone('+1 day');
        $beforeEndMidnight = (time() < $endMidnight->getTimestamp());
        return ($afterStartMidnight && $beforeEndMidnight);
    }
}
