<?php

declare(strict_types=1);

namespace FKSDB\Components\Game\Diplomas;

use FKSDB\Components\Grids\Components\BaseGrid;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\Fyziklani\TeamCategory;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use Fykosak\NetteORM\Selection\TypedGroupedSelection;
use Nette\DI\Container;

/**
 * @phpstan-extends BaseGrid<TeamModel2,array{}>
 */
class ResultsCategoryGrid extends BaseGrid
{
    private EventModel $event;
    private TeamCategory $category;

    public function __construct(EventModel $event, TeamCategory $category, Container $container)
    {
        parent::__construct($container);
        $this->event = $event;
        $this->category = $category;
    }

    protected function configure(): void
    {
        $this->paginate = false;
        $this->addSimpleReferencedColumns([
            '@fyziklani_team.fyziklani_team_id',
            '@fyziklani_team.name',
            '@fyziklani_team.rank_category',
        ]);
    }

    /**
     * @phpstan-return TypedGroupedSelection<TeamModel2>
     */
    protected function getModels(): TypedGroupedSelection
    {
        return $this->event->getParticipatingTeams()
            ->where('category', $this->category->value)
            ->order('fyziklani_team_id');
    }
}
