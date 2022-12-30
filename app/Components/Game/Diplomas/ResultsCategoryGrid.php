<?php

declare(strict_types=1);

namespace FKSDB\Components\Game\Diplomas;

use FKSDB\Components\Grids\Components\Grid;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\Fyziklani\TeamCategory;
use Nette\Database\Table\Selection;
use Nette\DI\Container;

class ResultsCategoryGrid extends Grid
{
    private EventModel $event;
    private TeamCategory $category;

    public function __construct(EventModel $event, TeamCategory $category, Container $container)
    {
        parent::__construct($container);
        $this->event = $event;
        $this->category = $category;
    }

    /**
     * @throws BadTypeException
     * @throws \ReflectionException
     */
    protected function configure(): void
    {
        $this->paginate = false;

        $this->addColumns([
            'fyziklani_team.fyziklani_team_id',
            'fyziklani_team.name',
            'fyziklani_team.rank_category',
        ]);
    }

    protected function getModels(): Selection
    {
        return $this->event->getParticipatingTeams()
            ->where('category', $this->category->value)
            ->order('name');
    }
}
