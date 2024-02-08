<?php

declare(strict_types=1);

namespace FKSDB\Components\Game\Diplomas;

use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use Fykosak\NetteORM\Selection\TypedGroupedSelection;

/**
 * @phpstan-extends BaseGrid<TeamModel2,array{}>
 */
class ResultsAnnouncementGrid extends ResultsWinnersGrid
{
    /**
     * @phpstan-return TypedGroupedSelection<TeamModel2>
     */
    protected function getModels(): TypedGroupedSelection
    {
        return $this->event->getParticipatingTeams()
            ->where('rank_category <= ?', 5)
            ->order('category, rank_category');
    }
}
